<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\MerchandiseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class MerchandiseItemFactory extends Factory
{
    protected $model = MerchandiseItem::class;

    protected static int $index = 0;

    protected static array $templates = [
        [
            'name' => 'Kaos Mantra in Summer',
            'image' => 'img/Event/KAOS MANTRA IN SUMMER.png',
            'description' => 'Kaos Mantra in Summer dirancang sebagai merchandise utama yang merepresentasikan suasana festival musim panas yang fun, santai, dan penuh warna.',
            'base_price' => 125000,
        ],
        [
            'name' => 'Kaos Zephyer',
            'image' => 'img/Event/KAOS ZEPHYER.png',
            'description' => "Bahan Kaos:\n- Cotton Combed 24s / 30s\n- Tekstur lembut dan halus di kulit\n- Menyerap keringat dengan baik, cocok untuk event outdoor\n- Nyaman dipakai seharian oleh berbagai usia (terutama mahasiswa)\n- Jahitan: Rantai (overlock + coverstitch) agar kuat dan tahan lama\n- Warna dasar: Biru tua / navy atau putih (menyesuaikan konsep laut & angin)\n\nDesain Visual Kaos:\n- Mengadaptasi ilustrasi ombak, kapal, dan ornamen nusantara sebagai identitas utama event.\n- Menggunakan palet warna biru laut dengan aksen hangat untuk menciptakan kesan dinamis dan artistik.\n- Menampilkan nuansa petualangan dan kebebasan yang merepresentasikan semangat Zephyer Project.",
            'base_price' => 135000,
        ],
        [
            'name' => 'Kaos RW3 Fest',
            'image' => 'img/Event/KAOS RW3 FEST.png',
            'description' => "Bahan:\n- Menggunakan cotton combed 24s\n- Tekstur halus, ringan, dan nyaman dipakai seharian\n- Menyerap keringat dengan baik, cocok untuk aktivitas event indoor maupun outdoor\n\nDesain Visual:\n- Mengadaptasi identitas visual RW3 Fest dengan ornamen batik dan nuansa merah maroon yang kuat.\n- Logo “RW3 Fest” menjadi fokus utama dengan tipografi elegan dan mudah dikenali.\n- Detail ilustratif tradisional memberikan kesan budaya, eksklusif, dan berkelas tanpa terlihat berlebihan.",
            'base_price' => 145000,
        ],
        [
            'name' => 'Kaos Abrakadabra Konser',
            'image' => 'img/Event/KAOS ABRAKADABRA KONSER.png',
            'description' => "Bahan:\n- Menggunakan cotton combed 24s yang lembut, ringan, dan nyaman dipakai seharian.\n- Bahan menyerap keringat dengan baik sehingga cocok untuk aktivitas konser.\n- Jahitan rapi dan kuat, tidak mudah melar meskipun sering dicuci.\n\nDesain Visual:\n- Mengangkat tema sulap dan fantasi melalui ilustrasi topi penyihir, tongkat sihir, dan elemen magis.\n- Palet warna ungu dominan dipadukan dengan aksen emas untuk memberikan kesan magis, elegan, dan misterius.\n- Tipografi “Abrakadabra Konser” dibuat tegas dan artistik sebagai fokus utama desain.\n- Komposisi visual disederhanakan agar tetap menarik saat diaplikasikan pada media kaos tanpa elemen informasi acara.",
            'base_price' => 130000,
        ],
        [
            'name' => 'Tote bag Mantra in Summer',
            'image' => 'img/Event/TOTEBAG MANTRA IN SUMMER.png',
            'description' => 'Tote bag Mantra in Summer dirancang sebagai merchandise fungsional yang tetap kuat secara visual dan identitas brand event.',
            'base_price' => 65000,
        ],
        [
            'name' => 'Tote bag Zephyer',
            'image' => 'img/Event/TOTEBAG ZEPHYER.png',
            'description' => "Bahan Tote Bag:\n- Canvas / Blacu Tebal\n- Kuat dan tidak mudah sobek\n- Ramah lingkungan (eco-friendly)\n- Bisa digunakan berulang kali\n- Handle: Jahitan reinforced agar kuat menahan beban\n\nDesain Visual Tote Bag:\n- Mengadaptasi ilustrasi dan warna dari desain kaos agar konsisten secara branding.\n- Menyederhanakan elemen visual kapal dan ombak agar tetap jelas pada media tas.\n- Nuansa eksplorasi dan estetika laut tetap terasa meskipun diaplikasikan pada tote bag.",
            'base_price' => 75000,
        ],
        [
            'name' => 'Tote Bag RW3 Fest',
            'image' => 'img/Event/TOTEBAG RW3 FEST.png',
            'description' => "Bahan:\n- Menggunakan canvas tebal (12–14 oz)\n- Kuat, tidak mudah robek, dan ramah lingkungan\n- Cocok digunakan untuk membawa buku, laptop, atau kebutuhan harian\n\nDesain Visual:\n- Mengadaptasi ilustrasi dan warna dari desain kaos agar konsisten secara branding.\n- Menampilkan logo RW3 Fest dan elemen arsitektur tradisional dengan komposisi seimbang.\n- Nuansa etnik-modern tetap terasa, membuat tote bag fungsional sekaligus estetik sebagai merchandise event.",
            'base_price' => 85000,
        ],
    ];

    public function definition(): array
    {
        $i = self::$index;
        self::$index = (self::$index + 1) % count(self::$templates);

        $tpl = self::$templates[$i];

        return [
            'event_id' => Event::factory(),
            'name' => $tpl['name'],
            'description' => $tpl['description'],
            'base_price' => $tpl['base_price'],
            'image' => $tpl['image'],
            'is_available' => true,
        ];
    }
}
