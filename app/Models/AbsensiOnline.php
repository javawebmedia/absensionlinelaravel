<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiOnline extends Model
{
    protected $table = 'absensi_online';

    protected $primaryKey = 'id_absensi_online';

    protected $fillable = [
        'id_pegawai',
        'tanggal',
        'waktu_absen',

        'latitude',
        'longitude',

        'latitude_kantor',
        'longitude_kantor',

        'jarak',

        'foto',
        'status'
    ];

    public function pegawai()
    {
        return $this->belongsTo(
            Pegawai::class,
            'id_pegawai'
        );
    }
}