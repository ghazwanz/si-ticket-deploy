<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    protected $model = Event::class;

    protected static int $index = 0;

    /**
     * Predefined base templates from docs/EventFormat.md.
     */
    protected static array $templates = [
        [
            'name_pattern' => 'Mantra in Summer {suffix}',
            'description' => 'Mantra in Summer adalah konser musik bertema musim panas yang menghadirkan suasana ceria, hangat, dan penuh energi. Konser ini menjadi ruang untuk bernyanyi bersama, merayakan kebersamaan, dan menikmati musik dalam balutan “Summer Vibes” yang ringan dan menyenangkan',
            'venues' => ['Lapangan Sepak Bola Gelora Benteng, PUSDIKZI Lawang Gintung', 'Lapangan Gasibu', 'Stadion Mandala Krida', 'Lapangan Rampal', 'Lapangan Pancasila', 'Stadion Utama Gelora Bung Karno'],
            'cities' => ['Bogor', 'Bandung', 'Yogyakarta', 'Malang', 'Semarang', 'Jakarta'],
            'banner' => 'img/Event/EVENT MANTRA IN SUMMER.jpg',
            'category' => 'Konser',
        ],
        [
            'name_pattern' => 'Zephyer Project Vol. {volume}',
            'description' => 'Zephyer Project Vol. {volume} merupakan ajang berkumpulnya warga {city} untuk melepas penat, mengekspresikan diri dan menunjukkan eksistensi diri dengan berdendang dan bergoyang bersama Denny Caknan, NDX AKA, Aftershine, Warga Koplo, dan Spontan Music💃🕺',
            'venues' => ['Grand Kamala Lagoon', 'Summarecon Mall Serpong', 'Margo City', 'JungleLand', 'Stadion Singaperbangsa', 'Ancol'],
            'cities' => ['Bekasi', 'Tangerang', 'Depok', 'Bogor', 'Karawang', 'Jakarta'],
            'banner' => 'img/Event/EVENT ZEPHYER.jpg',
            'category' => 'Konser',
        ],
        [
            'name_pattern' => 'RW3 Festival {suffix}',
            'description' => "RW3 Festival merupakan konser perayaan Hari Kemerdekaan yang mengangkat semangat Nusantara melalui musik, hiburan, dan kebersamaan dalam satu malam penuh perayaan. Menghadirkan konsep konser dengan sentuhan budaya dan nuansa khas kemerdekaan Indonesia, RW3 Festival menjadi ruang bagi masyarakat dan generasi muda untuk menikmati pengalaman hiburan yang meriah, hangat, dan berkesan.\n\nMelalui penampilan musisi, tata panggung, serta atmosfer perayaan yang dikemas secara modern, RW3 Festival hadir sebagai bagian dari semangat merayakan kemerdekaan dengan cara yang lebih besar, lebih hidup, dan penuh kebanggaan terhadap Indonesia.",
            'venues' => ['Lapangan Brigif 15 Kujang', 'Lapangan Saparua', 'Lapangan Kerkof', 'Lapangan Yon Armed', 'Lapangan Persikas', 'Lapangan Pacuan Kuda'],
            'cities' => ['Cimahi', 'Bandung', 'Garut', 'Purwakarta', 'Subang', 'Sumedang'],
            'banner' => 'img/Event/EVENT RW3 FESTIVAL.jpg',
            'category' => 'Konser',
        ],
        [
            'name_pattern' => 'Abrakadabra Konser {city}',
            'description' => 'Abrakadabra Konser 2026 hadir sebagai destinasi hiburan berkualitas sekaligus ruang apresiasi musik bagi masyarakat {city} dan sekitarnya. Melalui Abrakadabra Konser 2026, kami tidak hanya bertujuan memberikan pengalaman menonton konser yang meriah dan profesional, tetapi juga turut mendorong perputaran ekonomi kreatif serta pemberdayaan UMKM lokal di sekitar lokasi acara.',
            'venues' => ['Jl. Tridaya Barat', 'Alun-Alun Cirebon', 'Lapangan GGM', 'Alun-Alun Kuningan', 'Alun-Alun Subang', 'Lapangan Karangpawitan'],
            'cities' => ['Indramayu', 'Cirebon', 'Majalengka', 'Kuningan', 'Subang', 'Karawang'],
            'banner' => 'img/Event/ABRAKADABRA KONSER.jpg',
            'category' => 'Konser',
        ],
        [
            'name_pattern' => 'Singphoria {city}',
            'description' => 'Singphoria adalah kolaborasi aksi kreatif dan musik yang sudah menggelar tour di beberapa kota di Indonesia. Di {city}, Singphoria sudah masuk ke Vol. {volume} yang akan digelar pada Konsep yang lebih Fresh dan lebih Seru dengan Guest Star yang tidak kalah seru dibanding Singphoria {city} sebelumnya. Amankan tiketnya dari sekarang!!',
            'venues' => ['PKOR Way Halim', 'Benteng Kuto Besak', 'Lapangan Kantor Gubernur', 'Stadion Semarak', 'Stadion Utama Riau', 'Lapangan Merdeka'],
            'cities' => ['Lampung', 'Palembang', 'Jambi', 'Bengkulu', 'Pekanbaru', 'Medan'],
            'banner' => 'img/Event/EVENT SINGPHORIA LAMPUNG.jpg',
            'category' => 'Konser',
        ],
        [
            'name_pattern' => 'Riuh Aksara Festival {city}',
            'description' => 'Riuh Aksara adalah ruang temu bagi kata, suara, dan makna. Sebuah perayaan di mana aksara tidak hanya dibaca, tetapi dirasakan digaungkan lewat diskusi, pertunjukan, dan ekspresi kreatif lintas medium. Dalam riuhnya gagasan dan beragam latar belakang, setiap suara mendapat tempat untuk hadir dan didengar. Event ini lahir dari keyakinan bahwa bahasa dan ekspresi budaya memiliki kekuatan untuk menyatukan, memantik dialog, serta membuka cara pandang baru. Riuh Aksara menjadi wadah bagi individu dan komunitas untuk berbagi cerita, menafsirkan realitas, dan merayakan keberagaman melalui karya.',
            'venues' => ['Jl. Kolonel Basyir Surya', 'Alun-Alun Ciamis', 'Taman Kota Banjar', 'Lapangan Ketapang Doyong', 'Alun-Alun Sumedang', 'Lapangan Kerkof'],
            'cities' => ['Tasikmalaya', 'Ciamis', 'Banjar', 'Pangandaran', 'Sumedang', 'Garut'],
            'banner' => 'img/Event/RIUH AKSARA FESTIVAL.jpg',
            'category' => 'Seni',
        ],
        [
            'name_pattern' => 'Euphiverse Live {city}',
            'description' => 'Euphoria Life Festival bukan lagi sekadar festival, ia adalah sebuah perjalanan. Tempat di mana musik bukan hanya didengar, tetapi menjadi denyut yang menghidupkan setiap momen dari siang hingga malam. Di sini, tawa, air mata, dan pelukan tak perlu dijelaskan, cukup dirasakan. Dari gemerlap panggung hingga sudut-sudut penuh cerita, setiap langkah menyimpan alasan untuk kembali. Ada yang datang untuk bernyanyi bersama, ada yang ingin sejenak melupakan dunia, dan banyak yang hanya ingin merasa hidup. Namun satu hal yang pasti, tak ada yang pulang dengan hati yang sama. Kini, kami membawa nama Euphiverse Live dengan experience dari kreator yang merancang panggung Euphoria Life Festival menjadi meriah. Dengan sesuatu yang lebih besar dan lebih megah. Sebuah perayaan musik dengan panggung spektakuler yang akan mengguncang Kota {city}. Kami membawa pengalaman yang lebih dekat, lebih hangat, dan lebih berkesan, menyatukan energi ribuan orang dalam satu malam yang akan sulit dilupakan. Karena Euphiverse Live bukan hanya tentang siapa yang tampil di atas panggung, tetapi tentang apa yang kamu rasakan ketika lampu mulai meredup, musik mulai mengambil alih, dan seluruh suasana berubah menjadi kenangan yang akan terus hidup di dalam hati.',
            'venues' => ['Grage City Mall Cirebon', 'Stadion Karangbirahi', 'Stadion Yos Sudarso', 'Stadion Hoegeng', 'Stadion Sirandu', 'Lap. Dracik'],
            'cities' => ['Cirebon', 'Brebes', 'Tegal', 'Pekalongan', 'Pemalang', 'Batang'],
            'banner' => 'img/Event/EUPHIVERSE LIVE.jpg',
            'category' => 'Konser',
        ],
        [
            'name_pattern' => 'Voltaria Fest {suffix}',
            'description' => 'Voltaria Fest {suffix} Merupakan Festival Musik Kampus yang diselenggarakan oleh Himpunan Mahasiswa Elektro Universitas Jenderal Achmad Yani (UNJANI) sebagai wadah hiburan, kreativitas, dan ekspresi bagi generasi muda. “Get Loud. Get Lit."',
            'venues' => ['Unjani Cimahi', 'ITB Bandung', 'Unpad Jatinangor', 'UI Depok', 'IPB Bogor', 'UIN Jakarta'],
            'cities' => ['Cimahi', 'Bandung', 'Jatinangor', 'Depok', 'Bogor', 'Tangerang'],
            'banner' => 'img/Event/VOLTARIA FEST.jpg',
            'category' => 'Konser',
        ],
        [
            'name_pattern' => 'Singphoria Lombok {suffix}',
            'description' => 'Singphoria adalah kolaborasi aksi kreatif dan musik yang sudah menggelar tour di beberapa kota di Indonesia. Di Lombok sendiri, Singphoria {suffix} kembali hadir dengan Konsep yang lebih Fresh dan lebih Seru dengan Guest Star yang tidak kalah seru dibanding Singphoria LGP sebelumnya. Amankan tiketnya dari sekarang!!',
            'venues' => ['Ex. Bandara Selaparang', 'Lap. Karang Pule', 'Stadion Gelora Sandubaya', 'Lap. Serasuba', 'Lap. Merdeka', 'Stadion Oepoi'],
            'cities' => ['Lombok', 'Mataram', 'Sumbawa', 'Bima', 'Dompu', 'Kupang'],
            'banner' => 'img/Event/SINGPHORIA LGP (LOMBOK).jpg',
            'category' => 'Konser',
        ],
    ];

    public function definition(): array
    {
        $i = self::$index;
        self::$index = (self::$index + 1) % 50; // loop up to 50 distinct events

        $templateIndex = $i % count(self::$templates);
        $tpl = self::$templates[$templateIndex];

        $varIndex = (int) ($i / count(self::$templates));

        $city = $tpl['cities'][$varIndex % count($tpl['cities'])];
        $venue = $tpl['venues'][$varIndex % count($tpl['venues'])];
        $volume = 4 + $varIndex;
        $suffixList = ['2026', '2027', 'Vol. 5', 'Vol. 6', 'Special Edition', 'Autumn Tour'];
        $suffix = $suffixList[$varIndex % count($suffixList)];

        $name = str_replace(['{suffix}', '{volume}', '{city}'], [$suffix, $volume, $city], $tpl['name_pattern']);
        $description = str_replace(['{suffix}', '{volume}', '{city}'], [$suffix, $volume, $city], $tpl['description']);

        // Distribute dates sequentially over the next 180 days to avoid overlapping
        $date = Carbon::now()->addDays(15 + ($i * 4))->format('Y-m-d');

        // Resolve or create category
        $category = EventCategory::where('name', $tpl['category'])->first();
        $categoryId = $category ? $category->id : EventCategory::factory()->create([
            'name' => $tpl['category'],
            'slug' => Str::slug($tpl['category']),
        ])->id;

        return [
            'organizer_id' => User::factory()->organizer(),
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(5),
            'description' => $description,
            'banner_image' => $tpl['banner'],
            'venue_name' => $venue,
            'address' => $venue.', '.$city,
            'city' => $city,
            'event_date' => $date,
            'start_time' => '17:00:00',
            'end_time' => '22:00:00',
            'status' => $this->faker->randomElement(EventStatus::cases()),
            'is_featured' => $varIndex === 0 && $templateIndex < 3,
        ];
    }

    /**
     * Set event status to draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Draft,
        ]);
    }

    /**
     * Set event status to awaiting approval.
     */
    public function awaitingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::AwaitingApproval,
        ]);
    }

    /**
     * Set event status to published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Published,
        ]);
    }

    /**
     * Set event status to awaiting cancellation.
     */
    public function awaitingCancellation(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::AwaitingCancellation,
        ]);
    }

    /**
     * Set event status to completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Completed,
        ]);
    }

    /**
     * Set event status to cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Cancelled,
        ]);
    }

    /**
     * Set event as featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
