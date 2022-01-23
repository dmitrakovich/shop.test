<?= '<?xml version="1.0" encoding="UTF-8"?>', PHP_EOL ?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="$tdate">
    <shop>
        <name>modny.by</name>
        <company>ИП Ермаков И.В.</company>
        <url>$host/</url>
        $currency
        <delivery-options>
            <option cost="0" days="$dayDelivery" />
        </delivery-options>
    </shop>
</yml_catalog>
