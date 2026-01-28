<?php
namespace App\Controller\admin;

use App\Entity\Playlist;
use App\Form\PlaylistType;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use App\Repository\PlaylistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class AdminPlaylistsController extends AbstractController
{
    private $playlistPage = "pages/admin/admin.playlists.html.twig";

    /**
     * @var PlaylistRepository
     */
    private $playlistRepository;
    private $formationRepository;
    private $categorieRepository;

    public function __construct(PlaylistRepository $playlistRepository, FormationRepository $formationRepository, CategorieRepository $categorieRepository)
    {
        $this->playlistRepository = $playlistRepository;
        $this->formationRepository = $formationRepository;
        $this->categorieRepository = $categorieRepository;
    }

    #[Route('/admin/playlists', name: 'admin.playlists')]
    public function index() : Response
    {
        $playlists = $this->playlistRepository->findAllOrderByName('ASC');
        return $this->render($this->playlistPage, [
            "playlists" => $playlists,
        ]);
    }

    #[Route('/admin/playlists/delete/{id}', name:'admin.playlists.remove')]
    public function remove($id){
        $playlist = $this->playlistRepository->find($id);
        if($this->playlistRepository->countFormationsByPlaylist($playlist) > 0){
            return $this->redirectToRoute('admin.playlists');
        }

        $this->playlistRepository->remove($playlist);
        return $this->redirectToRoute('admin.playlists');
    }

    #[Route('/admin/playlists/modifier/{id}', name:'admin.playlists.modifier')]
    public function modifier(Request $request, int $id){
        $playlist = $this->playlistRepository->find($id);
        $formModifierPlaylist = $this->createForm(PlaylistType::class, $playlist);
        $formModifierPlaylist->handleRequest($request);

        $formations = $this->formationRepository->findAllForOnePlaylist($id);

        if($formModifierPlaylist->isSubmitted() && $formModifierPlaylist->isValid()){
            $this->playlistRepository->add($playlist);
            $this->addFlash('success', 'La playlist a bien été modifiée !');
            return $this->redirectToRoute('admin.playlists');
        }

        return $this->render('pages/admin/admin.addPlaylist.html.twig', [
            'formModifierPlaylist'=> $formModifierPlaylist->createView(),
            'formations'=> $formations,
        ]);
    }

    #[Route('/admin/playlists/create', name:'admin.playlists.create')]
    public function create(Request $request){
        $playlist = new Playlist;
        $formCreerPlaylist = $this->createForm(PlaylistType::class, $playlist);
        $formCreerPlaylist->handleRequest($request);

        $formations = [];

        if($formCreerPlaylist->isSubmitted() && $formCreerPlaylist->isValid()){
            $this->playlistRepository->add($playlist);
            $this->addFlash('success', 'La playlist a bien été créée !');
            return $this->redirectToRoute('admin.playlists');
        }

        return $this->render('pages/admin/admin.addPlaylist.html.twig', [
            'formModifierPlaylist'=> $formCreerPlaylist->createView(),
            'formations'=> $formations,
        ]);
    }

    #[Route('/admin/playlists/sort/{champ}/{ordre}', name:'admin.playlists.sort')]
    public function sort(string $champ, string $ordre){
        //
        $playlists = $this->playlistRepository->findAllOrderByName($ordre);
        return $this->render($this->playlistPage, [
            "playlists" => $playlists,
        ]);
    }

    #[Route('/admin/playlists/recherche/{champ}/{table}', name: 'adminplaylists.findallcontain')]
    public function findAllContain($champ, Request $request, $table=""): Response{
        $valeur = $request->get("recherche");
        $playlists = $this->playlistRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();

        $nombreFormations = [];
        foreach ($playlists as $p) {
            $nombreFormations[$p->getId()] = $this->playlistRepository->countFormationsByPlaylist($p);
        }

        return $this->render($this->playlistPage, [
            'playlists' => $playlists,
            'categories' => $categories,
            'valeur' => $valeur,
            'table' => $table,
            'nombreFormations' => $nombreFormations
        ]);
    }
}