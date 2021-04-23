<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use App\Repository\PinRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PinsController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'app_home', methods:["GET"])]
    public function index(PinRepository $pinRepository): Response
    {
        $pins = $pinRepository->findBy([], ['createdAt' => 'DESC']);
        return $this->render('pins/index.html.twig', compact('pins'));
    }

    #[Route('/pins/{id<[0-9]+>}', name:"app_pins_show", methods:["GET"])]
    public function show(Pin $pin): Response 
    {
        return $this->render('pins/show.html.twig', compact('pin'));
    }

    #[Route("/pins/create", name:"app_pins_create", methods:["GET", "POST"])]
    /**
     * @Security("is_granted('ROLE_USER') && user.isVerified()")
     */
    public function create(Request $request, UserRepository $userRepo): response
    {
        
        $pin = new Pin;
        $form = $this->createForm(PinType::class, $pin);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $pin->setUser($this->getUser());
            $this->em->persist($pin);
            $this->em->flush();
            $this->addFlash('success', "Pin successfully created");
            return $this->redirectToRoute('app_home');
        }
        
        
        return $this->render('pins/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route("/pins/{id<[0-9]+>}/edit", name:"app_pins_edit", methods:["GET", "PUT"])]
    /**
     * @Security("is_granted('PIN_EDIT', pin)")
     */
    public function edit(Pin $pin, Request $request):Response
    {
        
        if($pin->getUser() !== $this->getUser()){
            throw $this->createAccessDeniedException("forbidden action");
        }

        if(!$this->getUser()){
            throw $this->createAccessDeniedException('you need to login or register');
        }

        if(!$this->getUser()->isVerified()){
            throw $this->createAccessDeniedException('you need to verify your account');
        }


        $form = $this->createForm(PinType::class, $pin, [
            'method' => "PUT"
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->em->flush();
            $this->addFlash("success", "Pin successfully edited");
            return $this->redirectToRoute('app_home');
        }
        return $this->render('pins/edit.html.twig',[
            'form' => $form->createView(),
            'pin' => $pin
        ]);
    }

    #[Route("/pins/{id<[0-9]+>}", name:"app_pins_delete", methods:["DELETE"])]
    /**
     * @Security("is_granted('ROLE_USER') and user.isVerified() and pin.getUser() == user")
     */
    public function delete(Pin $pin, Request $request):Response
    {
        if(!$this->getUser()){
            throw $this->createAccessDeniedException('you need to login or register');
        }
        
        if($pin->getUser() !== $this->getUser()){
            throw $this->createAccessDeniedException("forbidden action");
        }

        if(!$this->getUser()->isVerified()){
            throw $this->createAccessDeniedException('you need to verify your account');
        }


        if($this->isCsrfTokenValid('pin_deletion_'.$pin->getId(), $request->request->get('csrf_token'))){
            $this->em->remove($pin);
            $this->em->flush();
            $this->addFlash("info", "Pin successfully deleted");
        }
        return $this->redirectToRoute("app_home");
    }
}

