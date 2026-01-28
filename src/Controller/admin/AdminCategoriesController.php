<?php
namespace App\Controller\admin;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminCategoriesController extends AbstractController
{
    /**
     * @var CategorieRepository
     */
    private $categorieRepository;

    public function __construct(CategorieRepository $categorieRepository)
    {
        $this->categorieRepository = $categorieRepository;
    }

    #[Route('/admin/categories', name:'admin.categories')]
    public function index(){
        $categories = $this->categorieRepository->findAll();
        $count = [];
        foreach ($categories as $categorie) {
            $count[$categorie->getId()] = $this->categorieRepository->countFormationsByCategorie($categorie);
        }

        return $this->render('pages/admin/admin.categories.html.twig', [
            'categories'=> $categories,
            'usageCounts' => $count,
        ]);
    }

    #[Route('/admin/categories/remove/{id}', name:'admin.categories.remove')]
    public function remove(int $id){
        $categorie = $this->categorieRepository->find($id);
        $count = $this->categorieRepository->countFormationsByCategorie($categorie);
        
        if($count == 0){
            $this->categorieRepository->remove($categorie);
            return $this->redirectToRoute('admin.categories');
        }
        return $this->redirectToRoute('admin.categories');
    }

    #[Route('admin/categories/add', name:'admin.categories.add')]
    public function add(Request $request){
        $name = $request->request->get('name');
        $token = $request->request->get('_token');
        
        if (!$this->isCsrfTokenValid('filtre_title', $token)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        if ($name) {
            $category = new Categorie();
            $category->setName($name);

            $this->categorieRepository->add($category);

            $this->addFlash('success', 'Catégorie ajoutée !');
        }
        return $this->redirectToRoute('admin.categories');
    }
}