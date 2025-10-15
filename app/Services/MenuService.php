<?php

namespace App\Services;

use Illuminate\Support\Collection;

class MenuService
{
    /**
     * Menerima daftar datar menu, memprosesnya, dan membangun struktur akhir yang benar.
     * @param Collection $flatMenuList Data mentah menu yang diizinkan untuk user.
     * @return Collection
     */
    public function processAndBuildMenu(Collection $flatMenuList): Collection
    {
        if ($flatMenuList->isEmpty()) {
            return collect();
        }

        // [KUNCI 1] "Kamus" ini menerjemahkan ID ke nama kategori.
        // Ini adalah satu-satunya bagian yang perlu Anda update jika ada departemen baru.
        $categoryMap = [
            1 => 'Sekolah',
            2 => 'Kasir',
            3 => 'Akunting',
            4 => 'Admin',
            5 => 'Keuangan', // Gabungan Keuangan Siswa & Guru
            6 => 'Persediaan',
        ];

        // Kelompokkan menu yang BENAR-BENAR dimiliki user berdasarkan application_id
        $groupedByApp = $flatMenuList->groupBy('application_id');

        $finalStructure = new Collection();
        
        // Urutkan berdasarkan urutan kunci di kamus agar urutan menu konsisten
        $sortedGroupKeys = collect($categoryMap)->keys();
        foreach ($sortedGroupKeys as $appId) {
            // [KUNCI 2] Hanya proses jika user memiliki menu di kategori ini
            if ($groupedByApp->has($appId)) {
                $menusForApp = $groupedByApp[$appId];
                
                // Ambil nama dari kamus dan potong jadi 1 kata
                $categoryName = explode(' ', $categoryMap[$appId])[0];

                // Bangun pohon menu multi-level untuk grup ini
                $menuTree = $this->buildTree($menusForApp);

                // Simpan hasilnya ke struktur akhir
                $finalStructure->push((object) [
                    'category_name' => $categoryName,
                    'menu_tree' => $menuTree
                ]);
            }
        }

        return $finalStructure;
    }

    /**
     * Fungsi rekursif untuk membangun pohon dari daftar datar (sudah benar).
     * @param Collection $elements
     * @param int $parentId
     * @return Collection
     */
    private function buildTree(Collection $elements, int $parentId = 0): Collection
    {
        $branch = new Collection();
        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                $children = $this->buildTree($elements, $element->id);
                if ($children->isNotEmpty()) {
                    $element->children = $children;
                } else {
                    $element->children = new Collection();
                }
                $branch->push($element);
            }
        }
        return $branch;
    }
}