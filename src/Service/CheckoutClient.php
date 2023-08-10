<?php

namespace App\Service;

use App\Entity\Product;
use Checkout\CheckoutApi;
use Checkout\CheckoutSdk;
use Checkout\Common\AccountHolder;
use Checkout\Common\Address;
use Checkout\Common\Currency;
use Checkout\Common\CustomerRequest;
use Checkout\Common\ShippingInfo;
use Checkout\Environment;
use Checkout\Payments\Request\PaymentRequest;
use Checkout\Payments\Request\Source\Apm\RequestGiropaySource;
use Checkout\Payments\Request\Source\RequestTokenSource;
use Checkout\Tokens\GooglePayTokenData;
use Checkout\Tokens\GooglePayTokenRequest;
use Symfony\Component\Routing\RouterInterface;

class CheckoutClient
{
    private RouterInterface $router;
    private string $processingChannelId;
    private CheckoutApi $checkoutAPI;

    public function __construct(RouterInterface $router, string $checkoutPrivateKey, string $processingChannelId)
    {
        $this->router = $router;
        $this->processingChannelId = $processingChannelId;

        $this->checkoutAPI = CheckoutSdk::builder()->staticKeys()
            ->secretKey($checkoutPrivateKey)
            ->environment(Environment::sandbox())
            ->build();
    }

    public function charge(Product $product, string $tokenId, string $customerName, string $email): array
    {
        $requestTokenSource = new RequestTokenSource();
        $requestTokenSource->token = $tokenId;

        $customerRequest = new CustomerRequest();
        $customerRequest->name = $customerName;
        $customerRequest->email = $email;

        $request = new PaymentRequest();
        $request->source = $requestTokenSource;
        $request->amount = $product->getPrice();
        $request->currency = Currency::$EUR;
        $request->processing_channel_id = $this->processingChannelId;
        $request->customer = $customerRequest;

        return $this->checkoutAPI->getPaymentsClient()->requestPayment($request);
    }

    public function createGiropayPaymentRequest(Product $product, string $firstname, string $lastname, string $email): array
    {
        $accountHolder = new AccountHolder();
        $accountHolder->first_name = $firstname;
        $accountHolder->last_name = $lastname;
        $accountHolder->email = $lastname;

        $requestGiropaySource = new RequestGiropaySource();
        $requestGiropaySource->account_holder = $accountHolder;

        $customerRequest = new CustomerRequest();
        $customerRequest->name = $firstname.' '.$lastname;
        $customerRequest->email = $email;

        // update with the customer's details
        $address = new Address();
        $address->city = 'Berlin';
        $address->zip = '10101';
        $address->country = 'DE';

        $shipping = new ShippingInfo();
        $shipping->address = $address;

        $request = new PaymentRequest();
        $request->source = $requestGiropaySource;
        $request->amount = $product->getPrice();
        $request->currency = Currency::$EUR;
        $request->processing_channel_id = $this->processingChannelId;
        $request->customer = $customerRequest;
        $request->shipping = $shipping;
        $request->success_url = $this->router->generate('app_checkout_giropay_success', [], RouterInterface::ABSOLUTE_URL);
        $request->failure_url = $this->router->generate('app_checkout_giropay_fail', [], RouterInterface::ABSOLUTE_URL);

        return $this->checkoutAPI->getPaymentsClient()->requestPayment($request);
    }
}
