<?php
namespace App\Controller\admin;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class AdminFormationsController extends AbstractController
{
    /**
     * @var FormationRepository
     */
    private $formationRepository;
    private $categorieRepository;

    private $adminPage = "pages/admin/admin.formations.html.twig";

    public function __construct(FormationRepository $formationRepository, CategorieRepository $categorieRepository)
    {
        $this->formationRepository = $formationRepository;
        $this->categorieRepository = $categorieRepository;
    }

    #[Route('/admin', name: 'admin.formations')]
    public function index() : Response
    {
        $formations = $this->formationRepository->findAllOrderBy('publishedAt', 'DESC');
        $categories = $this->categorieRepository->findAll();
        return $this->render("pages/admin/admin.formations.html.twig", [
            'formations' => $formations,
            'categories' => $categories
        ]);
    }

    #[Route('/admin/formations/tri/{champ}/{ordre}/{table}', name: 'admin.formations.sort')]
    public function sort($champ, $ordre, $table=""): Response{
        $formations = $this->formationRepository->findAllOrderBy($champ, $ordre, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render($this->adminPage, [
            'formations' => $formations,
            'categories' => $categories
        ]);
    }

    #[Route('/admin/recherche/{champ}/{table}', name: 'admin.formations.findallcontain')]
    public function findAllContain($champ, Request $request, $table=""): Response{
        $valeur = $request->get("recherche");
        $formations = $this->formationRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render($this->adminPage, [
            'formations' => $formations,
            'categories' => $categories,
            'valeur' => $valeur,
            'table' => $table
        ]);
    }

    #[Route('/admin/creerFormation', name: 'admin.creerFormation')]
    public function afficherCreerFormation(Request $request) : Response{
        $formation = new Formation();
        $formCreateFormation = $this->createForm(FormationType::class, $formation);
        $formCreateFormation->handleRequest($request);

        if($formCreateFormation->isSubmitted() && $formCreateFormation->isValid()){
            $this->formationRepository->add($formation);
            $this->addFlash('success', 'La formation a bien été créée !');
            return $this->redirectToRoute('admin.formations');
        }

        return $this->render('pages/admin/admin.addFormation.html.twig', [
            'formCreateFormation'=> $formCreateFormation->createView()
        ]);
    }

    #[Route('admin/formations/modifier/{id}', name: 'admin.formations.modifier')]
    public function modifier(Request $request, int $id){
        $formation = $this->formationRepository->find($id);

        $formModifierFormation = $this->createForm(FormationType::class, $formation);
        $formModifierFormation->handleRequest($request);

        if($formModifierFormation->isSubmitted() && $formModifierFormation->isValid()){
            $this->formationRepository->add($formation);
            $this->addFlash('success', 'La formation a bien été créée !');
            return $this->redirectToRoute('admin.formations');
        }

        return $this->render('pages/admin/admin.addFormation.html.twig', [
            'formCreateFormation'=> $formModifierFormation->createView()
        ]);
    }

    #[Route('/admin/remove/{id}', name: 'admin.formations.remove')]
    public function remove(int $id)
    {
        $formation = $this->formationRepository->find($id);
        $this->formationRepository->remove($formation);
        return $this->redirectToRoute('admin.formations');
    }
}
