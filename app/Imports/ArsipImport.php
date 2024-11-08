<?php

namespace App\Imports;

use App\Models\Arsip;
use App\Models\Riwayat;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ArsipImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if ($row['branch'] == '0209') {
            $kode = $row['deal_type'] . '-' . $row['deal_ref'];
            $existingArsip = Arsip::where('kode', $kode)->first();

            if ($existingArsip) {
                return null;
            }
            $today = Carbon::today();

            $tanggal_masuk = Date::excelToDateTimeObject($row['contract_date'])->format('Y-m-d');
            $tanggal_mulai = Date::excelToDateTimeObject($row['start_date'])->format('Y-m-d');
            $tanggal_selesai = Date::excelToDateTimeObject($row['mat_date'])->format('Y-m-d');

            if ($tanggal_selesai <= $today) {
                $items = Arsip::whereDate('tanggal_selesai', '<=', $today)->get();
                $status = '1';
            } else {
                $status = '0';
            }

            $arsip = Arsip::create([
                'kode' => $kode,
                'cif' => $row['cif'],
                'nama_lengkap' => $row['short_name'],
                'tanggal_mulai' => $tanggal_mulai,
                'tanggal_selesai' => $tanggal_selesai,
                'status' => $status,
            ]);

            $riwayat = Riwayat::create([
                'arsip_id' => $arsip->id,
                'jenis' => 'Masuk',
                'tanggal' => $tanggal_masuk,
                'catatan' => 'Arsip Masuk',
            ]);

            return $arsip;
        } else {
            return null;
        }
    }
}
