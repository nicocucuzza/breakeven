<?php

$c4 = file_get_contents("http://www.carrefour.com.ar/");
libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML($c4,LIBXML_NOBLANKS);

$links = array();
$uls = $doc->getElementsByTagName("ul");
for($i=0;$i<$uls->length;$i++){
    $ul = $uls->item($i);
    if($ul->nodeType === XML_ELEMENT_NODE){
        if($ul->getAttribute("class") == "level0"){
            foreach($ul->childNodes as $li){
                if($li->nodeType === XML_ELEMENT_NODE){

                    if(substr($li->getAttribute("class"),0,7) == "level1 ") {
                        foreach ($li->childNodes as $a) {
                            if ($a->tagName == "a"){
                                if($a->nodeType === XML_ELEMENT_NODE) {
                                    $link = $a->getAttribute("href");
                                    if (substr($link, 0, 4) == "http")
                                        $links[] = $link;
                                }
                            }
                        }
                    }
                 }
            }
        }
    }
}

foreach($link as $link) {
    $c4 = file_get_contents($link);

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($c4,LIBXML_NOBLANKS);

    $as = $doc->getElementsByTagName("a");
    for ($i = 0; $i < $as->length; $i++) {
        $a = $as->item($i);
        $categoria = trim($a->getAttribute("data-gua-ec-category"));
        if (strlen($categoria) > 1)
            break;
    }

    $divs = $doc->getElementsByTagName("div");

    $productos = array();
    $XML = new DOMDocument();
    for ($i = 0; $i < $divs->length; $i++) {
        $div = $divs->item($i);
        switch ($div->getAttribute('class')) {
            case "product-info":
                $producto = array();
                $producto['categoria'] = $categoria;
                foreach ($div->childNodes as $child) {
                    if ($child->nodeType === XML_ELEMENT_NODE) {
                        switch ($child->tagName) {
                            case "h2":

                                $producto['nombre'] = $child->textContent;
                                break;
                            case "h3":
                                $producto['fabricante'] = $child->textContent;
                                break;
                            case "div":
                                switch ($child->getAttribute("class")) {
                                    case "price-box":
                                        foreach ($child->childNodes as $precios) {
                                            if ($precios->nodeType === XML_ELEMENT_NODE) {
                                                if ($precios->tagName == "span")
                                                    if ($precios->getAttribute("class") == "regular-price")
                                                        $producto['precio'] = trim($precios->textContent) . "\n";
                                            }

                                        }
                                        break;
                                }
                                break;
                        }
                    }

                }
                $productos[] = $producto;
                break;
            default:
                break;
        }

    }
}
//$prod2 = array_unique($productos);
print_r($productos);
exit;

?>
