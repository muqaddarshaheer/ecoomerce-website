<?php

function shop_brands(): array
{
    return [
        [
            'slug' => 'dollar',
            'name' => 'Dollar',
            'place' => 'Pakistan',
            'line' => 'Pens and notebooks',
            'about' => 'Dollar is one of the best-known Pakistani writing names. This shelf holds the pens and notebooks Paperlane sells with it.',
            'codes' => ['0200003', '0200004', '0200006', '0200007', '0200008', '0200010'],
        ],
        [
            'slug' => 'piano',
            'name' => 'Piano',
            'place' => 'Pakistan',
            'line' => 'School pencils and files',
            'about' => 'Piano pens are an everyday sight in Pakistani school shops. This shelf holds pencils, a file box, blocks and a plush.',
            'codes' => ['0200001', '0200002', '0200005', '0200009'],
        ],
        [
            'slug' => 'deer',
            'name' => 'Deer',
            'place' => 'Pakistan',
            'line' => 'Gift boxes',
            'about' => 'Deer is a Pakistani marker and pen name. This shelf holds the ribboned gift boxes.',
            'codes' => ['0100001', '0100002', '0100003', '0100004', '0100005', '0100006'],
        ],
        [
            'slug' => 'dux',
            'name' => 'Dux',
            'place' => 'Pakistan',
            'line' => 'Candles and frames',
            'about' => 'Dux pencils are made for school work. This shelf holds the candle, frames and the remaining gift boxes.',
            'codes' => ['0100007', '0100008', '0100009', '0100010'],
        ],
        [
            'slug' => 'goldfish',
            'name' => 'Goldfish',
            'place' => 'Pakistan',
            'line' => 'Shoulder bags',
            'about' => 'Goldfish is known for school geometry sets. This shelf holds everyday shoulder bags and a slim wallet.',
            'codes' => ['0300001', '0300002', '0300003', '0300004', '0300005'],
        ],
        [
            'slug' => 'kidco',
            'name' => 'Kidco',
            'place' => 'Pakistan',
            'line' => 'Bags for the day',
            'about' => 'Kidco makes school stationery for children. This shelf holds backpacks, satchels and top-handle bags.',
            'codes' => ['0300006', '0300007', '0300008', '0300009', '0300010'],
        ],
        [
            'slug' => 'sayyed',
            'name' => 'Sayyed',
            'place' => 'Pakistan',
            'line' => 'Ties, wallets and handbags',
            'about' => 'Sayyed pencils are sold for Pakistani classrooms. This shelf holds ties, wallets and handbags.',
            'codes' => ['0400028', '0400029', '0400030', '0400031', '0400032', '0400067', '0400068', '0400069', '0400073'],
        ],
        [
            'slug' => 'naulakha',
            'name' => 'Naulakha',
            'place' => 'Pakistan',
            'line' => 'Gift baskets',
            'about' => 'Naulakha is a long-standing stationery name in local markets. This shelf holds the gift baskets and wrapped bouquets.',
            'codes' => ['0400033', '0400034', '0400035', '0400036', '0400070', '0400071'],
        ],
        [
            'slug' => 'uhu',
            'name' => 'UHU',
            'place' => 'Sold across Pakistan',
            'line' => 'Makeup',
            'about' => 'UHU is the glue stick many Pakistani classrooms use. This shelf holds lip colour, mascara and the eye palette.',
            'codes' => ['0400001', '0400004', '0400025', '0400059', '0400060', '0400072'],
        ],
        [
            'slug' => 'myco',
            'name' => 'Myco',
            'place' => 'Pakistan',
            'line' => 'Perfume',
            'about' => 'Myco is an everyday ballpoint in Pakistani stationery shops. This shelf holds the perfume bottles, including the labelled ones.',
            'codes' => ['0400002', '0400027', '0400074', '0400075', '0400076', '0400077'],
        ],
        [
            'slug' => 'bahadur',
            'name' => 'Bahadur',
            'place' => 'Pakistan',
            'line' => 'Nails',
            'about' => 'Bahadur is a local name on simple school supplies. This shelf holds the nail colours, each shown on a real hand.',
            'codes' => ['0400008', '0400024', '0400037', '0400038', '0400039', '0400040'],
        ],
        [
            'slug' => 'winner',
            'name' => 'Winner',
            'place' => 'Pakistan',
            'line' => 'Necklaces',
            'about' => 'Winner is a pen name sold for daily writing. This shelf holds the necklaces.',
            'codes' => ['0400006', '0400009', '0400041', '0400042', '0400043', '0400044', '0400045'],
        ],
        [
            'slug' => 'picasso',
            'name' => 'Picasso',
            'place' => 'Pakistan',
            'line' => 'Sets and earrings',
            'about' => 'Picasso pens are a familiar Pakistani writing name. This shelf holds necklace sets and earrings.',
            'codes' => ['0400012', '0400046', '0400047', '0400048', '0400049', '0400050', '0400051', '0400007', '0400013', '0400014', '0400062', '0400063', '0400064', '0400065', '0400066'],
        ],
        [
            'slug' => 'mercury',
            'name' => 'Mercury',
            'place' => 'Pakistan',
            'line' => 'Jhumkas, bracelets and watches',
            'about' => 'Mercury is another writing name on the local shelf. This shelf holds jhumkas, bracelets and watches.',
            'codes' => ['0400015', '0400053', '0400010', '0400016', '0400017', '0400018', '0400055', '0400056', '0400057', '0400019', '0400020'],
        ],
        [
            'slug' => 'camel',
            'name' => 'Camel',
            'place' => 'Pakistan',
            'line' => 'Rings and shawls',
            'about' => 'Camel geometry sets show up at the start of term. This shelf holds rings and shawls.',
            'codes' => ['0400021', '0400022', '0400023', '0400058', '0400003', '0400061'],
        ],
    ];
}

function brand_mark(string $slug, string $name): string
{
    $styles = [
        'dollar' => ['#4a2e22', '32'],
        'piano' => ['#243e63', '14'],
        'deer' => ['#8d4b32', '18'],
        'dux' => ['#8d6b3a', '8'],
        'goldfish' => ['#c4842a', '20'],
        'kidco' => ['#1e3a32', '18'],
        'sayyed' => ['#6b2d3c', '16'],
        'naulakha' => ['#9a4e2c', '22'],
        'uhu' => ['#c4a035', '12'],
        'myco' => ['#7a3e55', '32'],
        'bahadur' => ['#c4653a', '16'],
        'winner' => ['#5a3d6b', '18'],
        'picasso' => ['#2f5f6a', '10'],
        'mercury' => ['#3d4f66', '32'],
        'camel' => ['#6e5436', '20'],
    ];
    $style = $styles[$slug] ?? ['#5c3a2e', '18'];
    $letter = strtoupper(substr($name, 0, 1));
    return '<svg class="brand-logo" viewBox="0 0 64 64" aria-hidden="true"><rect width="64" height="64" rx="' . $style[1] . '" fill="' . $style[0] . '"/><text x="32" y="42" text-anchor="middle" font-family="Georgia, serif" font-size="28" fill="#fff">' . e($letter) . '</text></svg>';
}

function brand_by_slug(string $slug): ?array
{
    foreach (shop_brands() as $brand) {
        if ($brand['slug'] === $slug) {
            return $brand;
        }
    }
    return null;
}
