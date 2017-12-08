<?php

namespace WeAreDe\TbcPay;

class TbcPayController
{
    private $root;
    private $SQLite3;
    private $TbcPayProcessor;

    public function __construct()
    {
        $this->root            = dirname(__FILE__).'/../../../';
        $this->SQLite3         = new \SQLite3($this->root.'orders.sqlite3');
        $this->TbcPayProcessor = new TbcPayProcessor(
            getenv('CERT_PATH'),
            getenv('CERT_KEY'),
            $_SERVER['REMOTE_ADDR']
        );
    }

    public function index()
    {
        $this->render('index');
    }

    public function start()
    {
        $amount   = input('amount');
        $name     = input('name');
        $for      = input('for');

        $response = $this->generateTransId($amount, $for);

        if (isset($response['TRANSACTION_ID'])) {
            $this->saveOrder([
                'trans_id' => $response['TRANSACTION_ID'],
                'status'   => 'created',
                'name'     => $name,
                'for'      => $for,
                'amount'   => $amount
            ]);
        }

        $this->render('start', $response);
    }

    public function ok()
    {
        $transId  = input('trans_id');

        $response = $this->TbcPayProcessor->get_transaction_result($transId);

        if ($response['RESULT'] === 'OK') {
            $this->updateOrder($transId, 'success');
            $this->render('ok.success', $response);
        } else {
            $note = json_encode($response);
            $this->updateOrder($transId, 'failed', $note);
            $this->render('ok.fail', $response);
        }
    }

    public function fail()
    {
        // NOTE: does TBC return trans_id on fail?
        // TODO: if yes updateOrder($transId, 'failed');
        $this->render('fail');
    }

    public function orders()
    {
        $query  = $this->SQLite3->query('SELECT trans_id, status, name, for, amount, note, created_at FROM orders');
        $orders = [];
        while($result = $query->fetchArray(SQLITE3_ASSOC)) {
           $orders[] = $result; 
        }
        if ($orders) {
            return response()->json($orders, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        } else {
            echo 'no entries yet!';
        }
    }

    public function closeBusinessDay()
    {
        $result = $this->TbcPayProcessor->close_day();
        return response()->json($result);
    }

    private function saveOrder($order)
    {
        $stmt = $this->SQLite3->prepare('INSERT INTO orders (trans_id, status, name, for, amount) VALUES (:trans_id, :status, :name, :for, :amount)');
        $stmt->bindValue(':trans_id', $order['trans_id'], SQLITE3_TEXT);
        $stmt->bindValue(':status', $order['status'], SQLITE3_TEXT);
        $stmt->bindValue(':name', $order['name'], SQLITE3_TEXT);
        $stmt->bindValue(':for', $order['for'], SQLITE3_TEXT);
        $stmt->bindValue(':amount', $order['amount'], SQLITE3_INTEGER);
        $stmt->execute();
    }

    private function updateOrder($transId, $status, $note = null)
    {
        $stmt = $this->SQLite3->prepare('UPDATE orders SET status = :status, note = :note WHERE trans_id = :trans_id');
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':note', $note, SQLITE3_TEXT);
        $stmt->bindValue(':trans_id', $transId, SQLITE3_TEXT);
        $stmt->execute();
    }

    private function generateTransId($amount, $for = null)
    {
        $this->TbcPayProcessor->amount      = $amount * 100;
        $this->TbcPayProcessor->currency    = 981;
        $this->TbcPayProcessor->description = $for;
        $this->TbcPayProcessor->language    = 'GE';

        return $this->TbcPayProcessor->sms_start_transaction();
    }

    private function render($view, $data = null)
    {
        include $this->root."views/$view.php";
    }
}
