<?php

namespace App\Services;

use Illuminate\Support\Collection;

class MenuService
{
    /**
     * Menerima daftar datar menu yang sudah terurut, memprosesnya, dan membangun struktur akhir.
     * @param Collection $flatMenuList Data mentah menu yang diizinkan untuk user.
     * @return Collection
     */
    public function processAndBuildMenu(Collection $flatMenuList): Collection
    {
        if ($flatMenuList->isEmpty()) {
            return collect();
        }

        // Kamus untuk menerjemahkan ID ke nama kategori, dengan urutan yang benar
        $categoryMap = [
            1 => 'Sekolah',
            2 => 'Kasir',
            5 => 'Keuangan',
            6 => 'Persediaan',
            3 => 'Akunting',
            4 => 'Admin',
        ];

        // Kelompokkan menu yang BENAR-BENAR dimiliki user berdasarkan application_id
        $groupedByApp = $flatMenuList->groupBy('application_id');

        $finalStructure = new Collection();
        
        // Loop berdasarkan urutan kustom di kamus
        foreach ($categoryMap as $appId => $categoryName) {
            // Hanya proses jika user memiliki menu di kategori ini
            if ($groupedByApp->has($appId)) {
                $menusForApp = $groupedByApp[$appId];
                
                // Ambil nama kategori dan potong jadi 1 kata
                $finalCategoryName = explode(' ', $categoryName)[0];

                // Bangun pohon menu multi-level untuk grup ini
                $menuTree = $this->buildTree($menusForApp);

                // Simpan hasilnya ke struktur akhir
                $finalStructure->push((object) [
                    'category_name' => $finalCategoryName,
                    'menu_tree' => $menuTree
                ]);
            }
        }

        return $finalStructure;
    }

    /**
     * Fungsi rekursif untuk membangun pohon dari daftar datar (private, sebagai helper).
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