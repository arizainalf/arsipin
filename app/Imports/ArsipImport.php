<?php

namespace App\Imports;

use App\Models\Arsip;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ArsipImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $kode = $row['deal_type'] . '-' . $row['deal_ref'];
        $existingArsip = Arsip::where('kode', $kode)->first();

        // Jika kode sudah ada, lewati penambahan data
        if ($existingArsip) {
            return null;
        }
        $today = Carbon::today();
        
        $tanggal_masuk = Date::excelToDateTimeObject($row['contract_date'])->format('Y-m-d');
        $tanggal_mulai = Date::excelToDateTimeObject($row['start_date'])->format('Y-m-d');
        $tanggal_selesai = Date::excelToDateTimeObject($row['mat_date'])->format('Y-m-d');

        if ($tanggal_selesai <= $today){
        $items = Arsip::whereDate('tanggal_selesai', '<=', $today)->get();
            $status = '1';
        }else{
            $status = '0';  
        }
        
        return new Arsip([
            'kode' => $kode,
            'cif' => $row['cif'],
            'nama_lengkap' => $row['short_name'],
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
            'status' => $status,
        ]);
    }
}
