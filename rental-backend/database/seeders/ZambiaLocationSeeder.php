<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZambiaLocationSeeder extends Seeder
{
    public function run(): void
    {
        $data = $this->zambiaData();

        foreach ($data as $provinceData) {
            $provinceId = DB::table('provinces')->insertGetId([
                'name'       => $provinceData['name'],
                'code'       => $provinceData['code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($provinceData['districts'] as $districtData) {
                $districtId = DB::table('districts')->insertGetId([
                    'province_id' => $provinceId,
                    'name'        => $districtData['name'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                foreach ($districtData['towns'] as $town) {
                    DB::table('towns')->insert([
                        'district_id' => $districtId,
                        'name'        => $town['name'],
                        'latitude'    => $town['lat'] ?? null,
                        'longitude'   => $town['lng'] ?? null,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }
        }
    }

    private function zambiaData(): array
    {
        return [
            [
                'name' => 'Central Province', 'code' => 'CEN',
                'districts' => [
                    ['name' => 'Chibombo',    'towns' => [['name' => 'Chibombo', 'lat' => -14.6667, 'lng' => 28.0667], ['name' => 'Chisamba', 'lat' => -14.9833, 'lng' => 28.35]]],
                    ['name' => 'Kabwe',       'towns' => [['name' => 'Kabwe', 'lat' => -14.4469, 'lng' => 28.4464], ['name' => 'Makululu', 'lat' => -14.45, 'lng' => 28.44]]],
                    ['name' => 'Kapiri Mposhi','towns' => [['name' => 'Kapiri Mposhi', 'lat' => -13.9667, 'lng' => 28.6833]]],
                    ['name' => 'Mkushi',      'towns' => [['name' => 'Mkushi', 'lat' => -13.6333, 'lng' => 29.3833]]],
                    ['name' => 'Mumbwa',      'towns' => [['name' => 'Mumbwa', 'lat' => -14.9833, 'lng' => 27.0667]]],
                    ['name' => 'Serenje',     'towns' => [['name' => 'Serenje', 'lat' => -13.2333, 'lng' => 30.2167]]],
                    ['name' => 'Chisamba',    'towns' => [['name' => 'Chisamba', 'lat' => -14.9833, 'lng' => 28.35]]],
                    ['name' => 'Chitambo',    'towns' => [['name' => 'Chitambo', 'lat' => -13.0, 'lng' => 30.2]]],
                    ['name' => 'Itezhi-Tezhi','towns' => [['name' => 'Itezhi-Tezhi', 'lat' => -15.7333, 'lng' => 26.0167]]],
                    ['name' => 'Luano',       'towns' => [['name' => 'Luano', 'lat' => -14.5, 'lng' => 29.5]]],
                    ['name' => 'Ngabwe',      'towns' => [['name' => 'Ngabwe', 'lat' => -14.3, 'lng' => 27.5]]],
                ],
            ],
            [
                'name' => 'Copperbelt Province', 'code' => 'CBE',
                'districts' => [
                    ['name' => 'Chingola',    'towns' => [['name' => 'Chingola', 'lat' => -12.5328, 'lng' => 27.8564], ['name' => 'Nchanga', 'lat' => -12.5, 'lng' => 27.85]]],
                    ['name' => 'Chililabombwe','towns' => [['name' => 'Chililabombwe', 'lat' => -12.3667, 'lng' => 27.8333]]],
                    ['name' => 'Kalulushi',   'towns' => [['name' => 'Kalulushi', 'lat' => -12.8333, 'lng' => 28.1167]]],
                    ['name' => 'Kitwe',       'towns' => [['name' => 'Kitwe', 'lat' => -12.8028, 'lng' => 28.2132], ['name' => 'Nkana', 'lat' => -12.8, 'lng' => 28.2], ['name' => 'Parklands', 'lat' => -12.79, 'lng' => 28.21]]],
                    ['name' => 'Luanshya',    'towns' => [['name' => 'Luanshya', 'lat' => -13.1333, 'lng' => 28.4167], ['name' => 'Roan', 'lat' => -13.14, 'lng' => 28.4]]],
                    ['name' => 'Lufwanyama', 'towns' => [['name' => 'Lufwanyama', 'lat' => -13.0, 'lng' => 27.2]]],
                    ['name' => 'Masaiti',     'towns' => [['name' => 'Masaiti', 'lat' => -13.0833, 'lng' => 28.7667]]],
                    ['name' => 'Mpongwe',     'towns' => [['name' => 'Mpongwe', 'lat' => -13.5, 'lng' => 28.15]]],
                    ['name' => 'Mufulira',    'towns' => [['name' => 'Mufulira', 'lat' => -12.55, 'lng' => 28.2333], ['name' => 'Kantanshi', 'lat' => -12.55, 'lng' => 28.23]]],
                    ['name' => 'Ndola',       'towns' => [['name' => 'Ndola', 'lat' => -12.9587, 'lng' => 28.6366], ['name' => 'Kansenshi', 'lat' => -12.96, 'lng' => 28.63], ['name' => 'Itawa', 'lat' => -12.97, 'lng' => 28.64]]],
                ],
            ],
            [
                'name' => 'Eastern Province', 'code' => 'EAS',
                'districts' => [
                    ['name' => 'Chadiza',     'towns' => [['name' => 'Chadiza', 'lat' => -14.0667, 'lng' => 32.4333]]],
                    ['name' => 'Chama',       'towns' => [['name' => 'Chama', 'lat' => -11.1833, 'lng' => 33.2]]],
                    ['name' => 'Chipata',     'towns' => [['name' => 'Chipata', 'lat' => -13.6428, 'lng' => 32.6444], ['name' => 'Kapata', 'lat' => -13.64, 'lng' => 32.64]]],
                    ['name' => 'Katete',      'towns' => [['name' => 'Katete', 'lat' => -14.1, 'lng' => 32.05]]],
                    ['name' => 'Lundazi',     'towns' => [['name' => 'Lundazi', 'lat' => -12.3, 'lng' => 33.1833]]],
                    ['name' => 'Mambwe',      'towns' => [['name' => 'Mambwe', 'lat' => -13.3333, 'lng' => 31.9333]]],
                    ['name' => 'Nyimba',      'towns' => [['name' => 'Nyimba', 'lat' => -14.5594, 'lng' => 30.8139]]],
                    ['name' => 'Petauke',     'towns' => [['name' => 'Petauke', 'lat' => -14.25, 'lng' => 31.3333]]],
                    ['name' => 'Sinda',       'towns' => [['name' => 'Sinda', 'lat' => -14.0, 'lng' => 32.0]]],
                    ['name' => 'Vubwi',       'towns' => [['name' => 'Vubwi', 'lat' => -13.9, 'lng' => 32.75]]],
                    ['name' => 'Lumezi',      'towns' => [['name' => 'Lumezi', 'lat' => -12.0, 'lng' => 33.0]]],
                    ['name' => 'Msanzala',    'towns' => [['name' => 'Msanzala', 'lat' => -14.8, 'lng' => 31.1]]],
                ],
            ],
            [
                'name' => 'Luapula Province', 'code' => 'LUA',
                'districts' => [
                    ['name' => 'Chembe',      'towns' => [['name' => 'Chembe', 'lat' => -12.5667, 'lng' => 28.6833]]],
                    ['name' => 'Chiengi',     'towns' => [['name' => 'Chiengi', 'lat' => -8.7, 'lng' => 29.15]]],
                    ['name' => 'Chipili',     'towns' => [['name' => 'Chipili', 'lat' => -10.7167, 'lng' => 29.1333]]],
                    ['name' => 'Kawambwa',    'towns' => [['name' => 'Kawambwa', 'lat' => -9.7944, 'lng' => 29.0794]]],
                    ['name' => 'Mansa',       'towns' => [['name' => 'Mansa', 'lat' => -11.1, 'lng' => 28.8667], ['name' => 'Pamodzi', 'lat' => -11.1, 'lng' => 28.87]]],
                    ['name' => 'Milenge',     'towns' => [['name' => 'Milenge', 'lat' => -11.6, 'lng' => 29.5]]],
                    ['name' => 'Mwense',      'towns' => [['name' => 'Mwense', 'lat' => -10.3667, 'lng' => 28.7]]],
                    ['name' => 'Nchelenge',   'towns' => [['name' => 'Nchelenge', 'lat' => -9.35, 'lng' => 28.7333]]],
                    ['name' => 'Samfya',      'towns' => [['name' => 'Samfya', 'lat' => -11.3667, 'lng' => 29.55]]],
                    ['name' => 'Lunga',       'towns' => [['name' => 'Lunga', 'lat' => -10.5, 'lng' => 29.1]]],
                    ['name' => 'Mwansabombwe','towns' => [['name' => 'Mwansabombwe', 'lat' => -9.1, 'lng' => 28.9]]],
                ],
            ],
            [
                'name' => 'Lusaka Province', 'code' => 'LSK',
                'districts' => [
                    ['name' => 'Chilanga',    'towns' => [['name' => 'Chilanga', 'lat' => -15.5528, 'lng' => 28.2736], ['name' => 'Kafue', 'lat' => -15.7667, 'lng' => 28.1833]]],
                    ['name' => 'Chongwe',     'towns' => [['name' => 'Chongwe', 'lat' => -15.3333, 'lng' => 28.6833], ['name' => 'Kanakantapa', 'lat' => -15.2, 'lng' => 29.0]]],
                    ['name' => 'Kafue',       'towns' => [['name' => 'Kafue', 'lat' => -15.7667, 'lng' => 28.1833]]],
                    ['name' => 'Luangwa',     'towns' => [['name' => 'Luangwa', 'lat' => -15.5667, 'lng' => 30.4167]]],
                    ['name' => 'Lusaka',      'towns' => [
                        ['name' => 'Lusaka CBD',       'lat' => -15.4167, 'lng' => 28.2833],
                        ['name' => 'Kabulonga',        'lat' => -15.4236, 'lng' => 28.3081],
                        ['name' => 'Woodlands',        'lat' => -15.3914, 'lng' => 28.3258],
                        ['name' => 'Rhodes Park',      'lat' => -15.4036, 'lng' => 28.3272],
                        ['name' => 'Chelston',         'lat' => -15.3792, 'lng' => 28.3494],
                        ['name' => 'Roma',             'lat' => -15.4072, 'lng' => 28.2919],
                        ['name' => 'Ibex Hill',        'lat' => -15.3617, 'lng' => 28.3711],
                        ['name' => 'Chilenje',         'lat' => -15.435,  'lng' => 28.34],
                        ['name' => 'Matero',           'lat' => -15.395,  'lng' => 28.27],
                        ['name' => 'Kalingalinga',     'lat' => -15.41,   'lng' => 28.33],
                        ['name' => 'Mtendere',         'lat' => -15.44,   'lng' => 28.37],
                        ['name' => 'Emmasdale',        'lat' => -15.41,   'lng' => 28.29],
                        ['name' => 'Longacres',        'lat' => -15.405,  'lng' => 28.295],
                        ['name' => 'Northmead',        'lat' => -15.38,   'lng' => 28.32],
                        ['name' => 'Avondale',         'lat' => -15.375,  'lng' => 28.315],
                        ['name' => 'Makeni',           'lat' => -15.47,   'lng' => 28.28],
                        ['name' => 'Lilayi',           'lat' => -15.51,   'lng' => 28.27],
                        ['name' => 'Chilulu',          'lat' => -15.42,   'lng' => 28.32],
                        ['name' => 'Olympia',          'lat' => -15.39,   'lng' => 28.3],
                        ['name' => 'Levy Junction',    'lat' => -15.42,   'lng' => 28.3],
                    ]],
                    ['name' => 'Rufunsa',     'towns' => [['name' => 'Rufunsa', 'lat' => -15.1167, 'lng' => 29.6667]]],
                    ['name' => 'Shibuyunji',  'towns' => [['name' => 'Shibuyunji', 'lat' => -15.5, 'lng' => 27.5]]],
                ],
            ],
            [
                'name' => 'Muchinga Province', 'code' => 'MUC',
                'districts' => [
                    ['name' => 'Chinsali',    'towns' => [['name' => 'Chinsali', 'lat' => -10.5533, 'lng' => 32.0786]]],
                    ['name' => 'Isoka',       'towns' => [['name' => 'Isoka', 'lat' => -10.15, 'lng' => 32.6333]]],
                    ['name' => 'Mpika',       'towns' => [['name' => 'Mpika', 'lat' => -11.8333, 'lng' => 31.45]]],
                    ['name' => 'Mporokoso',   'towns' => [['name' => 'Mporokoso', 'lat' => -9.3667, 'lng' => 30.1167]]],
                    ['name' => 'Nakonde',     'towns' => [['name' => 'Nakonde', 'lat' => -9.3667, 'lng' => 32.7333]]],
                    ['name' => 'Shiwang\'andu','towns' => [['name' => 'Shiwang\'andu', 'lat' => -11.2, 'lng' => 31.8]]],
                    ['name' => 'Lavushimanda','towns' => [['name' => 'Lavushimanda', 'lat' => -12.0, 'lng' => 31.0]]],
                    ['name' => 'Kanchibiya',  'towns' => [['name' => 'Kanchibiya', 'lat' => -11.5, 'lng' => 31.3]]],
                ],
            ],
            [
                'name' => 'Northern Province', 'code' => 'NOR',
                'districts' => [
                    ['name' => 'Chilubi',     'towns' => [['name' => 'Chilubi', 'lat' => -11.0, 'lng' => 29.95]]],
                    ['name' => 'Kaputa',      'towns' => [['name' => 'Kaputa', 'lat' => -8.4667, 'lng' => 29.6667]]],
                    ['name' => 'Kasama',      'towns' => [['name' => 'Kasama', 'lat' => -10.2142, 'lng' => 31.1842], ['name' => 'Thorn Park', 'lat' => -10.21, 'lng' => 31.18]]],
                    ['name' => 'Luwingu',     'towns' => [['name' => 'Luwingu', 'lat' => -10.2667, 'lng' => 29.9167]]],
                    ['name' => 'Mbala',       'towns' => [['name' => 'Mbala', 'lat' => -8.8333, 'lng' => 31.3667]]],
                    ['name' => 'Mporokoso',   'towns' => [['name' => 'Mporokoso', 'lat' => -9.3667, 'lng' => 30.1167]]],
                    ['name' => 'Mpulungu',    'towns' => [['name' => 'Mpulungu', 'lat' => -8.7667, 'lng' => 31.1167]]],
                    ['name' => 'Mungwi',      'towns' => [['name' => 'Mungwi', 'lat' => -10.15, 'lng' => 31.35]]],
                    ['name' => 'Senga Hill',  'towns' => [['name' => 'Senga Hill', 'lat' => -9.8833, 'lng' => 31.2333]]],
                    ['name' => 'Lupososhi',   'towns' => [['name' => 'Lupososhi', 'lat' => -10.0, 'lng' => 29.7]]],
                    ['name' => 'Lunte',       'towns' => [['name' => 'Lunte', 'lat' => -10.5, 'lng' => 30.8]]],
                ],
            ],
            [
                'name' => 'North-Western Province', 'code' => 'NWP',
                'districts' => [
                    ['name' => 'Chavuma',     'towns' => [['name' => 'Chavuma', 'lat' => -13.1, 'lng' => 22.7]]],
                    ['name' => 'Ikelenge',    'towns' => [['name' => 'Ikelenge', 'lat' => -11.3833, 'lng' => 24.2667]]],
                    ['name' => 'Kabompo',     'towns' => [['name' => 'Kabompo', 'lat' => -13.5917, 'lng' => 24.2]]],
                    ['name' => 'Kasempa',     'towns' => [['name' => 'Kasempa', 'lat' => -13.4667, 'lng' => 25.8333]]],
                    ['name' => 'Mufumbwe',    'towns' => [['name' => 'Mufumbwe', 'lat' => -13.55, 'lng' => 24.75]]],
                    ['name' => 'Mwinilunga',  'towns' => [['name' => 'Mwinilunga', 'lat' => -11.7383, 'lng' => 24.4325]]],
                    ['name' => 'Solwezi',     'towns' => [['name' => 'Solwezi', 'lat' => -12.1833, 'lng' => 26.3833], ['name' => 'Matelo', 'lat' => -12.18, 'lng' => 26.38]]],
                    ['name' => 'Zambezi',     'towns' => [['name' => 'Zambezi', 'lat' => -13.55, 'lng' => 23.1]]],
                    ['name' => 'Mushindamo',  'towns' => [['name' => 'Mushindamo', 'lat' => -12.3, 'lng' => 26.0]]],
                    ['name' => 'Kalumbila',   'towns' => [['name' => 'Kalumbila', 'lat' => -12.1, 'lng' => 25.6]]],
                ],
            ],
            [
                'name' => 'Southern Province', 'code' => 'SOU',
                'districts' => [
                    ['name' => 'Choma',       'towns' => [['name' => 'Choma', 'lat' => -16.8167, 'lng' => 26.9833]]],
                    ['name' => 'Gwembe',      'towns' => [['name' => 'Gwembe', 'lat' => -16.5, 'lng' => 27.6333]]],
                    ['name' => 'Itezhi-Tezhi','towns' => [['name' => 'Itezhi-Tezhi', 'lat' => -15.7333, 'lng' => 26.0167]]],
                    ['name' => 'Kalomo',      'towns' => [['name' => 'Kalomo', 'lat' => -17.0167, 'lng' => 26.4833]]],
                    ['name' => 'Kazungula',   'towns' => [['name' => 'Kazungula', 'lat' => -17.7833, 'lng' => 25.2667]]],
                    ['name' => 'Livingstone', 'towns' => [['name' => 'Livingstone', 'lat' => -17.8417, 'lng' => 25.8542], ['name' => 'Maramba', 'lat' => -17.84, 'lng' => 25.85], ['name' => 'Dambwa', 'lat' => -17.86, 'lng' => 25.86]]],
                    ['name' => 'Mazabuka',    'towns' => [['name' => 'Mazabuka', 'lat' => -15.8667, 'lng' => 27.7667]]],
                    ['name' => 'Monze',       'towns' => [['name' => 'Monze', 'lat' => -16.2833, 'lng' => 27.4833]]],
                    ['name' => 'Namwala',     'towns' => [['name' => 'Namwala', 'lat' => -15.75, 'lng' => 26.4333]]],
                    ['name' => 'Pemba',       'towns' => [['name' => 'Pemba', 'lat' => -16.5167, 'lng' => 27.3667]]],
                    ['name' => 'Siavonga',    'towns' => [['name' => 'Siavonga', 'lat' => -16.5333, 'lng' => 28.7167]]],
                    ['name' => 'Sinazongwe',  'towns' => [['name' => 'Sinazongwe', 'lat' => -17.2667, 'lng' => 27.4667]]],
                    ['name' => 'Zimba',       'towns' => [['name' => 'Zimba', 'lat' => -17.2333, 'lng' => 26.95]]],
                    ['name' => 'Chirundu',    'towns' => [['name' => 'Chirundu', 'lat' => -16.05, 'lng' => 28.8667]]],
                ],
            ],
            [
                'name' => 'Western Province', 'code' => 'WES',
                'districts' => [
                    ['name' => 'Kalabo',      'towns' => [['name' => 'Kalabo', 'lat' => -14.9944, 'lng' => 22.6817]]],
                    ['name' => 'Kaoma',       'towns' => [['name' => 'Kaoma', 'lat' => -14.8, 'lng' => 24.8]]],
                    ['name' => 'Limulunga',   'towns' => [['name' => 'Limulunga', 'lat' => -14.5667, 'lng' => 23.0667]]],
                    ['name' => 'Lukulu',      'towns' => [['name' => 'Lukulu', 'lat' => -14.3667, 'lng' => 23.2333]]],
                    ['name' => 'Mongu',       'towns' => [['name' => 'Mongu', 'lat' => -15.2833, 'lng' => 23.15], ['name' => 'Limulunga', 'lat' => -15.15, 'lng' => 23.13]]],
                    ['name' => 'Nalolo',      'towns' => [['name' => 'Nalolo', 'lat' => -15.7333, 'lng' => 23.1333]]],
                    ['name' => 'Senanga',     'towns' => [['name' => 'Senanga', 'lat' => -16.1167, 'lng' => 23.2667]]],
                    ['name' => 'Sesheke',     'towns' => [['name' => 'Sesheke', 'lat' => -17.475, 'lng' => 24.3083]]],
                    ['name' => 'Shangombo',   'towns' => [['name' => 'Shangombo', 'lat' => -16.2167, 'lng' => 22.2667]]],
                    ['name' => 'Mitete',      'towns' => [['name' => 'Mitete', 'lat' => -13.9, 'lng' => 23.0]]],
                    ['name' => 'Nkeyema',     'towns' => [['name' => 'Nkeyema', 'lat' => -14.8, 'lng' => 24.3]]],
                    ['name' => 'Sikongo',     'towns' => [['name' => 'Sikongo', 'lat' => -15.0, 'lng' => 22.5]]],
                ],
            ],
        ];
    }
}
