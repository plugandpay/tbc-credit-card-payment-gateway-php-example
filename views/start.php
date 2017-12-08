<html>
<head>
    <title>TBCPAY</title>
    <script type="text/javascript" language="javascript">
        function redirect() {
          document.returnform.submit();
        }
    </script>
</head>

<?php if (isset($data['error'])) { ?>

<body>
    <h2>Error:</h2>
    <h1><?php echo $data['error']; ?></h1>
</body>

<?php } elseif (isset($data['TRANSACTION_ID'])) { ?>

<body onLoad="javascript:redirect()">
    <form name="returnform" action="https://securepay.ufc.ge/ecomm2/ClientHandler" method="POST">
        <input type="hidden" name="trans_id" value="<?php echo $data['TRANSACTION_ID']; ?>">

        <noscript>
            <center>Please click the submit button below.<br>
            <input type="submit" name="submit" value="Submit"></center>
        </noscript>
    </form>
</body>

<?php } ?>

</html>
