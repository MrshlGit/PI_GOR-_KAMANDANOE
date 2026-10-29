<?php
include ("../controller/transaksi.php");
?>
<!DOCTYPE html>
<html>

<head>

    <title>Transaksi</title>

    <link rel="stylesheet" href="transaksi.css">

</head>

<body>

    <div class="container">

        <h1 style="margin-top:30px;">
            TAGIHAN PEMBAYARAN
        </h1>

        <?php
        while ($d = mysqli_fetch_array($data)) {
            ?>
            <div class="bungkus">
                <div class="payment-box">
                    <div class="data">

                        <h3>
                            <?php echo $d['lapangan']; ?>
                        </h3>

                        <p>
                            <span class="label">Tanggal</span>: <?php echo $d['tanggal']; ?>
                        </p>

                        <p>
                            <span class="label">Jam</span>: <?php echo $d['jam']; ?>
                        </p>



                        <?php
                        if ($d['status'] != "Lunas") {
                            ?>

                            <form method="POST">

                                <input type="hidden" name="id_booking" value="<?php echo $d['id_booking']; ?>">

                                <button type="submit" name="bayar" value="QRIS" class="payment-btn"
                                    onclick="this.form.metode.value='QRIS'">
                                    Bayar QRIS
                                </button>

                                <button type="submit" name="bayar" value="Cash" class="payment-btn"
                                    onclick="this.form.metode.value='Cash'">
                                    Bayar Cash
                                </button>

                                <input type="hidden" name="metode">

                            </form>

                        <?php } ?>
                    </div>

                    <div class="lapangan">

                        <?php
                        if ($d['lapangan'] == "Lapangan A") { ?>
                            <img src="../img/karpet2.jpg">

                        <?php } ?>

                        <?php
                        if ($d['lapangan'] == "Lapangan B") { ?>
                            <img src="../img/kayu2.jpg">
                        <?php } ?>

                    </div>

                </div>

                <div class="box2">
                    <p>
                      <span class="label">Total Bayar</span>: <b>Rp <?php echo number_format($d['total_bayar']); ?></b>
                    </p>

                    <p>
                       <span class="label">Status</span>: <b><?php echo $d['status']; ?></b>
                    </p>

                    <p>
                        <span class="label">Metode</span>: <?php echo $d['metode_pembayaran']; ?>
                    </p>

                </div>

            </div>
        <?php } ?>




    </div>

</body>

</html>