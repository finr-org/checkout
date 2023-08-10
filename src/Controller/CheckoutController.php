<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CheckoutClient;
use Checkout\CheckoutApiException;
use Checkout\CheckoutAuthorizationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{
    #[Route('/', name: 'app_checkout_index', methods: 'GET')]
    public function index(): Response
    {
        return $this->render("index.html.twig", [
            'product' => Product::generateTestProduct()
        ]);
    }

    #[Route('/charge', name: 'app_checkout_charge', methods: 'POST')]
    public function charge(Request $request, CheckoutClient $checkoutClient): JsonResponse
    {
        $tokenID = $request->request->get('token_id');
        $customerName = $request->request->get('customer_name');
        $email = $request->request->get('email');
        $product = Product::generateTestProduct();

        if (null === $tokenID || null === $customerName || null === $email) {
            return $this->json(['error' => 'mandatory parameter is missing'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $response = $checkoutClient->charge($product, $tokenID, $customerName, $email);

            return $this->json([
                'status' => $response['status']
            ]);
        } catch (CheckoutApiException|CheckoutAuthorizationException $e) {
            return $this->json([], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/giropay/create', name: 'app_checkout_giropay_create', methods: 'POST')]
    public function giropayCreate(Request $request, CheckoutClient $checkoutClient): JsonResponse
    {
        $firstname = $request->request->get('firstname');
        $lastname = $request->request->get('lastname');
        $email = $request->request->get('email');
        $product = Product::generateTestProduct();

        if (null === $firstname || null === $lastname || null === $email) {
            return $this->json(['error' => 'mandatory parameter is missing'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $response = $checkoutClient->createGiropayPaymentRequest($product, $firstname, $lastname, $email);

            return $this->json([
                'status' => $response['status'],
                'link' => $response['_links']['redirect']['href']
            ]);

        } catch (CheckoutApiException|CheckoutAuthorizationException $e) {
            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/giropay/success', name: 'app_checkout_giropay_success', methods: 'GET')]
    public function giropaySuccess(): Response
    {
        return $this->render('success.html.twig');
    }

    #[Route('/giropay/fail', name: 'app_checkout_giropay_fail', methods: 'GET')]
    public function giropayFail(): Response
    {
        return $this->render('fail.html.twig');
    }
}
