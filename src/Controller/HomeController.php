<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CarouselImageRepository;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(CarouselImageRepository $carouselImageRepository): Response
    {
        $carouselImages = $carouselImageRepository->findAllOrdered();
        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'carousel_images' => $carouselImages,
        ]);
    }
}
