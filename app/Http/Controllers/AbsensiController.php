<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AbsensiOnline;

class AbsensiController extends Controller
{

    public function index()
    {
        return view('absensi_online.index');
    }

    public function store(Request $request)
    {

        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'foto' => 'required|image'
        ]);

        $latUser = $request->latitude;
        $lonUser = $request->longitude;

        $latKantor = config('absensi.latitude_kantor');
        $lonKantor = config('absensi.longitude_kantor');

        $jarak = $this->hitungJarak(
            $latUser,
            $lonUser,
            $latKantor,
            $lonKantor
        );

        if ($jarak > config('absensi.radius_absen')) {

            return response()->json([
                'status' => false,
                'message' => 'Anda berada di luar radius kantor'
            ]);
        }

        // Upload Foto

        $file = $request->file('foto');

        $namaFile =
            time().'_'.$file->getClientOriginalName();

        $file->move(
            public_path('upload/absensi_online'),
            $namaFile
        );

        AbsensiOnline::create([

            'id_pegawai' => 1,

            'tanggal' => date('Y-m-d'),

            'waktu_absen' => now(),

            'latitude' => $latUser,
            'longitude' => $lonUser,

            'latitude_kantor' => $latKantor,
            'longitude_kantor' => $lonKantor,

            'jarak' => $jarak,

            'foto' => $namaFile,

            'status' => 'hadir'

        ]);

        return response()->json([
            'status' => true,
            'message' => 'Absensi berhasil'
        ]);
    }

    private function hitungJarak(
        $lat1,
        $lon1,
        $lat2,
        $lon2
    )
    {

        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLon / 2) *
            sin($dLon / 2);

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return $earthRadius * $c;
    }

}