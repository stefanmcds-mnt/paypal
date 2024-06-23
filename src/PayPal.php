<?php

namespace PayPal;

/**
 * All PayPal CheckOut V2 Details Class
 */

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersAuthorizeRequest;
use PayPalCheckoutSdk\Payments\AuthorizationsCaptureRequest;
use PayPalCheckoutSdk\Payments\CapturesRefundRequest;
use PayPalHttp\HttpException;

class PayPal
{

    /**
     * Create a new controller instance.
     *
     * @param array $config
     */
    public function __construct(?array $config)
    {
        /* setup PayPal V2 api context */
        // PayPal Environment
        define("PAYPAL_ENVIRONMENT", $config['mode']);
        // PayPal REST API endpoints
        define("PAYPAL_ENDPOINTS", $config['endpoint']);
        // PayPal REST App credentials
        define("PAYPAL_CREDENTIALS", [
            "client_id" => $config['client_id'],
            "client_secret" => $config['client_secret']
        ]);
        // PayPal Currency
        define("PAYPAL_CURRENCY", $config['currency']);
        // PayPal REST API version
        define("PAYPAL_REST_VERSION", $config['version']);
        // ButtonSource Tracker Code
        define("SBN_CODE", "PP-DemoPortal-EC-Psdk-ORDv2-php");
        // Set PHP variable
        ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
    }

    /**********************************************************/
    /**                  PAYPAL V2                            */
    /**********************************************************/

    /**
     * Returns PayPal HTTP client instance with environment that has access
     * credentials context. Use this instance to invoke PayPal APIs, provided the
     * credentials have access.
     */
    private static function client()
    {
        return new PayPalHttpClient(self::environment());
    }

    /**
     * Set up and return PayPal PHP SDK environment with PayPal access credentials.
     * This sample uses SandboxEnvironment. In production, use LiveEnvironment.
     */
    private static function environment()
    {
        //$clientId = PAYPAL_CREDENTIALS['client_id'];
        //$clientSecret = PAYPAL_CREDENTIALS['client_secret'];
        //return new SandboxEnvironment($clientId, $clientSecret);
        return new SandboxEnvironment(PAYPAL_CREDENTIALS['client_id'], PAYPAL_CREDENTIALS['client_secret']);
    }

    /**********************************************************/
    /**                  PAYPAL CHECKOUT V2                   */
    /**********************************************************/

    /**
     * Setting up the JSON request body for creating the order with minimum request body.
     *
     * @param object $dati order object
     * @return mixed
     */
    private static function buildRequestBody(object $dati = NULL)
    {
        /*
        $api = ['intent'];
        $api['application_context'] =  ['brand_name', 'locale', 'landing_page', 'shipping_preference', 'user_action'];
        $api['purchase_units'][0] =  ['reference_id', 'description', 'custom_id', 'soft_descriptor'];
        $api['purchase_units'][0]['amount'] =  ['currency_code', 'value'];
        $api['purchase_units'][0]['amount']['breakdown']['item_total'] =  ['currency_code', 'value'];
        $api['purchase_units'][0]['amount']['breakdown']['shipping'] =  ['currency_code', 'value'];
        $api['purchase_units'][0]['amount']['breakdown']['handling'] =  ['currency_code', 'value'];
        $api['purchase_units'][0]['amount']['breakdown']['tax_total'] =  ['currency_code', 'value'];
        $api['purchase_units'][0]['amount']['breakdown']['shipping_discount'] =  ['currency_code', 'value'];
        $api['purchase_units'][0]['items'] = [];
        $api['items'] = ['name', 'description', 'sku', 'quantity', 'category'];
        $api['purchase_units']['items']['unit_amount'] =  ['currency_code', 'value'];
        $api['purchase_units']['items']['tax'] =  ['currency_code', 'value'];
        $api['purchase_units']['shipping'] =  ['method'];
        $api['purchase_units']['shipping']['address'] =  ['address_line_1', 'address_line_2', 'admin_area_2', 'admin_area_1', 'postal_code', 'country_code'];
        $api = json_decode(json_encode($api), false);
        if (is_object($api)) {
            if (!is_null($dati)) {
                $api->intent = $dati->Intent;
                $api->application_context->brand_name = 'STEF@N MCDS S.a.s.';
                $api->application_context->locale = 'it-IT';
            }
        }
        */
        if (!is_null($dati)) {
            $return['intent'] = $dati->Intent;
            $return['application_context'] = [
                'brand_name' => 'STEF@N MCDS S.a.s.',
                'locale' => 'it-IT',
                'landing_page' => 'BILLING',
                'shipping_preferences' => 'NO_SHIPPING',
                'user_action' => 'PAY_NOW', // PAY_NOW | CONTINUE
                //'payment_method' => '',
                //'return_url' => env('APP_URL') . '/Payment',
                //'cancel_url' => env('APP_URL') . '/Payment'
                //'stored_payment_source' => '',
            ];
            $return['purchase_units'][0] = [
                'reference_id' => $dati->Token,
                //'description' => implode(' ', explode('\r\n', $dati->Oggetto)),
                'description' => $dati->Tipologia,
                'custom_id' => $dati->Tipo->numero,
                //'soft_descriptor' => $dati->Tipologia,
                //'invoice_id' => $dati->Token,
                'amount' => [
                    'currency_code' => PAYPAL_CURRENCY,
                    'value' => (isset($dati->Tipo->subtotale)) ? number_format(round($dati->Tipo->totale + $dati->Tipo->subtotale, 2), 2) : number_format(round($dati->Tipo->totale, 2), 2),
                    //'breakdown' => [
                    //    'item_total' => ['currency_code' => PAYPAL_CURRENCY, 'value' => number_format(round($dati->Tipo->totale, 2), 2),],
                    //    'shipping' => ['currency_code' => PAYPAL_CURRENCY, 'value' => (isset($dati->Tipo->spedizione)) ?  $dati->Tipo->spedizione : 0.00,],
                    //    'handling' => ['currency_code' => PAYPAL_CURRENCY, 'value' => (isset($dati->Tipo->gestione)) ? $dati->Tipo->gestione : 0.00,],
                    //    'insurance' => ['currency_code' => PAYPAL_CURRENCY, 'value' => (isset($dati->Tipo->assicurazione)) ? $dati->Tipo->assicurazione : 0.00,],
                    //    'tax_total' => ['currency_code' => PAYPAL_CURRENCY, 'value' => number_format(round(($dati->Tipo->totale * 0.22), 2), 2),],
                    //    'discount' => ['currency_code' => PAYPAL_CURRENCY, 'value' => (isset($dati->Tipo->sconto)) ? $dati->Tipo->sconto : 0.00,],
                    //    'shipping_discount' => ['currency_code' => PAYPAL_CURRENCY, 'value' => (isset($dati->Tipo->scontospedizione)) ? $dati->Tipo->scontospedizione : 0.00,],
                    //    ]
                ]
            ];
        } else {
            $return = [];
        }
        return json_encode($return);
    }

    /**
     * Setting up the JSON request body for creating the Order with minimum request body. The Intent in the
     * request body should be set as "AUTHORIZE" for authorize intent flow.
     *
     */
    private static function buildMinimumRequestBody(string $intent, object $dati = NULL)
    {
        return [
            'intent' => $intent,
            'application_context' => [
                'return_url' => 'https://example.com/return',
                'cancel_url' => 'https://example.com/cancel'
            ],
            'purchase_units' => [
                0 => [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => '220.00'
                    ]
                ]
            ]
        ];
    }

    /**
     * Like Payment for old version
     * CreateOrder::createOrder(true);
     * This is the sample function to create an order. It uses the
     *
     * JSON body returned by buildRequestBody() to create an order.
     *
     * @param object $dati
     * @param boolean $debug
     * @return \Illuminate\Http\Response
     */
    public static function createOrder(object $dati, $debug = false)
    {
        // 1. Create Order Request
        $request = new OrdersCreateRequest();
        $request->headers["prefer"] = "return=representation";
        //$request->headers["PayPal-Partner-Attribution-Id"] = "PARTNER_ID_ASSIGNED_BY_YOUR_PARTNER_MANAGER";
        // 2. Populate Body
        $request->body = self::buildRequestBody($dati);
        // 3. Call PayPal to set up a transaction
        $PayPal = self::client();
        $response = $PayPal->execute($request);
        //$response = (object) $PayPal->execute($request);
        //$content = $response->getOriginalContent();

        if ($debug) {
            $response = (object) $response;
            print "Status Code: {$response->statusCode}\n";
            print "Status: {$response->result->status}\n";
            print "Order ID: {$response->result->id}\n";
            print "Intent: {$response->result->intent}\n";
            print "Links:\n";
            foreach ($response->result->links as $link) {
                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            // To print the whole response body, uncomment the following line
            // echo json_encode($response->result, JSON_PRETTY_PRINT);
        }
        // 4. Return a successful response to the client.
        return json_encode($response);
        /* Default False execution */
        return FALSE;
    }

    /**
     * GetOrder::getOrder('REPLACE-WITH-ORDER-ID', true)
     * You can use this function to retrieve an order by passing order ID as an argument.
     *
     * @param string $orderID
     */
    public static function getOrder(string $orderId)
    {
        // 3. Call PayPal to get the transaction details
        $client = self::client();
        $response = (object) $client->execute(new OrdersGetRequest($orderId));
        /**
         *Enable the following line to print complete response as JSON.
         */
        //print json_encode($response->result);
        print "Status Code: {$response->statusCode}\n";
        print "Status: {$response->result->status}\n";
        print "Order ID: {$response->result->id}\n";
        print "Intent: {$response->result->intent}\n";
        print "Links:\n";
        foreach ($response->result->links as $link) {
            print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
        }
        // 4. Save the transaction in your database. Implement logic to save transaction to your database for future reference.
        // print "Gross Amount: {$response->result->purchase_units[0]->amount->currency_code} {$response->result->purchase_units[0]->amount->value}\n";
        return $response;
        // To print the whole response body, uncomment the following line
        // echo json_encode($response->result, JSON_PRETTY_PRINT);
    }

    /**
     * This function can be used to capture an order payment by passing the approved
     * order ID as argument.
     *
     * captureOrder('REPLACE-WITH-APPORVED-ORDER-ID', true)
     * @param string $orderId
     * @param boolean $debug
     * @returns
     */
    public static function captureOrder(string $orderId, $debug = false)
    {
        // 1. Create Order Capture Request
        $request = new OrdersCaptureRequest($orderId);
        $request->headers["prefer"] = "return=representation";
        // 2. Populate Body
        // $request->body = self::buildRequestBody();
        // 3. Call PayPal to capture an authorization
        $PayPal = self::client();
        $response = $PayPal->execute($request);
        // 4. Save the capture ID to your database. Implement logic to save capture to your database for future reference.
        if ($debug) {
            $response = (object) $response;
            print "Status Code: {$response->statusCode}\n";
            print "Status: {$response->result->status}\n";
            print "Order ID: {$response->result->id}\n";
            print "Links:\n";
            foreach ($response->result->links as $link) {
                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            print "Capture Ids:\n";
            foreach ($response->result->purchase_units as $purchase_unit) {
                foreach ($purchase_unit->payments->captures as $capture) {
                    print "\t{$capture->id}";
                }
            }
            // To print the whole response body, uncomment the following line
            // echo json_encode($response->result, JSON_PRETTY_PRINT);
        }
        return json_encode($response);
    }

    /**
     * authorizeOrder(&apos REPLACE-WITH-VALID-APPROVED-ORDER-ID', true)
     * Use this function to perform authorization on the approved order
     * Pass a valid, approved order ID as an argument.
     *
     * @param string $orderID  required
     * @param boolean $debug
     * @return response in json format
     */
    public static function authorizeOrder(string $orderId, $debug = false)
    {
        $request = new OrdersAuthorizeRequest($orderId);
        // $request->body = self::buildRequestBody();
        // 3. Call PayPal to authorize an order
        $client = self::client();
        $response = $client->execute($request);
        // 4. Save the authorization ID to your database. Implement logic to save authorization to your database for future reference.
        if ($debug) {
            $response = (object) $response;
            print "Status Code: {$response->statusCode}\n";
            print "Status: {$response->result->status}\n";
            print "Order ID: {$response->result->id}\n";
            print "Authorization ID: {$response->result->purchase_units[0]->payments->authorizations[0]->id}\n";
            print "Links:\n";
            foreach ($response->result->links as $link) {
                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            print "Authorization Links:\n";
            foreach ($response->result->purchase_units[0]->payments->authorizations[0]->links as $link) {
                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            // To toggle printing the whole response body comment/uncomment the following line
            //echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
        }
        return json_encode($response);
    }

    /**
     * captureAuth(&apos REPLACE-WITH-VALID-APPROVED-AUTH-ID', true)
     * Use the following function to capture Authorization.
     * Pass a valid authorization ID as an argument.
     */
    public static function captureAuthorize($OrderId, $debug = false)
    {
        $request = new AuthorizationsCaptureRequest($OrderId);
        // $request->body = self::buildRequestBody();
        // 3. Call PayPal to capture an authorization.
        $client = self::client();
        $response = (object) $client->execute($request);
        // 4. Save the capture ID to your database for future reference.
        if ($debug) {
            print "Status Code: {$response->statusCode}\n";
            print "Status: {$response->result->status}\n";
            print "Capture ID: {$response->result->id}\n";
            print "Links:\n";
            foreach ($response->result->links as $link) {
                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            // To toggle printing the whole response body comment/uncomment
            // the follwowing line
            echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
        }
        return $response;
    }

    /**
     * This function can be used to preform refund on the capture.
     */
    public static function refundOrder($OrdineId, $debug = false)
    {
        $request = new CapturesRefundRequest($OrdineId);
        $request->body = self::buildRequestBody();
        $client = self::client();
        $response = (object) $client->execute($request);

        if ($debug) {
            print "Status Code: {$response->statusCode}\n";
            print "Status: {$response->result->status}\n";
            print "Order ID: {$response->result->id}\n";
            print "Links:\n";
            foreach ($response->result->links as $link) {
                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            // To toggle printing the whole response body comment/uncomment below line
            echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
        }
        return $response;
    }

    public static function prettyPrint($jsonData, $pre = "")
    {
        $pretty = "";
        foreach ($jsonData as $key => $val) {
            $pretty .= $pre . ucfirst($key) . ": ";
            if (strcmp(gettype($val), "array") == 0) {
                $pretty .= "\n";
                $sno = 1;
                foreach ($val as $value) {
                    $pretty .= $pre . "\t" . $sno++ . ":\n";
                    $pretty .= self::prettyPrint($value, $pre . "\t\t");
                }
            } else {
                $pretty .= $val . "\n";
            }
        }
        return $pretty;
    }

    /**
     * Body has no required parameters (intent, purchase_units)
     */
    public static function createError1()
    {
        $request = new OrdersCreateRequest();
        $request->body = "{}";
        print "Request Body: {}\n\n";

        print "Response:\n";
        try {
            $client = self::client();
            $response = $client->execute($request);
        } catch (HttpException $exception) {
            $message = json_decode($exception->getMessage(), true);
            print "Status Code: {$exception->statusCode}\n";
            print(self::prettyPrint($message));
        }
    }

    /**
     * Body has invalid parameter value for intent
     */
    public static function createError2()
    {
        $request = new OrdersCreateRequest();
        $request->body = array(
            'intent' => 'INVALID',
            'purchase_units' =>
            array(
                0 =>
                array(
                    'amount' =>
                    array(
                        'currency_code' => 'USD',
                        'value' => '100.00',
                    ),
                ),
            ),
        );
        print "Request Body:\n" . json_encode($request->body, JSON_PRETTY_PRINT) . "\n\n";

        try {
            $client = self::client();
            $response = $client->execute($request);
        } catch (HttpException $exception) {
            print "Response:\n";
            $message = json_decode($exception->getMessage(), true);
            print "Status Code: {$exception->statusCode}\n";
            print(self::prettyPrint($message));
        }
    }
}
