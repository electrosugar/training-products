<h2><?= translateText('Order #') . $order['id'] ?> </h2>
<div class="product">
    <div class="info">
        <span class="title"><?= translateText('Name: ') . $order['name'] ?></span>
        <br>
        <span class="description"><?= translateText('Contact: ') . $order['contact'] ?></span>
        <br>
        <span class="price"><?= translateText('Comment: ') . $order['comment'] ?></span>
        <br>
        <span class="date"><?= translateText('Date: ') . $order['creation_date'] ?></span>
        <br>
    </div>
    <span><?= translateText('Total Price: ') . $order['totalPrice'] . getCurrency() ?></span>
</div>
