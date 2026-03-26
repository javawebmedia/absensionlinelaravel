<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8">
      <meta name="viewport"
         content="width=device-width, initial-scale=1">
      <meta name="csrf-token"
         content="{{ csrf_token() }}">
      <title>Absensi Online</title>
      <link href=
         "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
         rel="stylesheet">
      <link rel="stylesheet"
         href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
      <style>
         body{
         background:#f8f9fa;
         }
         #map{
         height:300px;
         border-radius:12px;
         }
         #previewFoto{
         width:100%;
         max-height:250px;
         object-fit:cover;
         border-radius:12px;
         display:none;
         margin-top:10px;
         }
         .card{
         border-radius:14px;
         }
         .status-box{
         font-size:14px;
         }
      </style>
   </head>
   <body>
      <div class="container mt-3 mb-5">
         <div class="card shadow-sm">
            <div class="card-body">
               <h5 class="mb-3">
                  <strong>📍 ABSENSI ONLINE</strong>
               </h5>
               <hr>
               <div id="map"></div>
               <div class="mt-3 status-box">
                  <div id="info">
                     Menunggu lokasi GPS...
                  </div>
               </div>
               <form id="formAbsen"
                  class="mt-3"
                  enctype="multipart/form-data">
                  <input type="hidden"
                     id="latitude"
                     name="latitude">
                  <input type="hidden"
                     id="longitude"
                     name="longitude">
                  <!-- FOTO -->
                  <label class="mt-2">
                  📷 Foto Bukti Absensi
                  </label>
                  <div class="row mb-3">
                     <div class="col-md-6">
                        <input
                           type="file"
                           name="foto"
                           id="foto"
                           accept="image/*"
                           capture="environment"
                           class="form-control mb-3"
                           required>
                        <img id="previewFoto">
                     </div>
                     <div class="col-md-6">
                        <button
                           type="submit"
                           id="btnAbsen"
                           class="btn btn-primary w-100"
                           disabled>
                        Absen Sekarang
                        </button>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
      <script src=
         "https://code.jquery.com/jquery-3.7.1.min.js"></script>
      <script src=
         "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <script>
         // ======================
         // CSRF TOKEN
         // ======================
         	$.ajaxSetup({
         		headers:{
         			'X-CSRF-TOKEN':
         			$('meta[name="csrf-token"]').attr('content')
         		}
         	});
         // ======================
         // CONFIG
         // ======================
         	const CONFIG = {
         		latKantor:
         		{{ config('absensi.latitude_kantor') }},
         		lonKantor:
         		{{ config('absensi.longitude_kantor') }},
         		radius:50,
         		maxFotoSize:
         		2 * 1024 * 1024         
         	};
         // ======================
         // GLOBAL VARIABLE
         // ======================
         	let map;
         	let markerUser;
         // ======================
         // ICON USER MERAH
         // ======================
         	const redIcon = new L.Icon({
         		iconUrl:
         		'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
         		shadowUrl:
         		'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
         		iconSize:[25,41],
         		iconAnchor:[12,41],
         		popupAnchor:[1,-34],
         		shadowSize:[41,41]
         	});
         // ======================
         // SWEET ALERT HELPER
         // ======================
         	function showSuccess(message){
         		Swal.fire({
         			icon:'success',
         			title:'Berhasil',
         			text:message,
         			confirmButtonColor:'#0d6efd'
         		});
         	}
         	function showError(message){
         		Swal.fire({
         			icon:'error',
         			title:'Gagal',
         			text:message,
         			confirmButtonColor:'#dc3545'
         		});
         	}
         	function showWarning(message){
         		Swal.fire({
         			icon:'warning',
         			title:'Peringatan',
         			text:message,
         			confirmButtonColor:'#ffc107'
         		});
         	}
         	function showLoading(){
         		Swal.fire({
         			title:'Memproses...',
         			text:'Sedang menyimpan absensi',
         			allowOutsideClick:false,
         			didOpen:()=>{
         				Swal.showLoading();
         			}
         		});
         	}
         // ======================
         // INIT MAP
         // ======================
         	function initMap(){
         		map = L.map('map')
         		.setView(
         			[CONFIG.latKantor, CONFIG.lonKantor],
         			18
         			);
         		L.tileLayer(
         			'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
         			).addTo(map);
         // Marker kantor
         		L.marker(
         			[CONFIG.latKantor, CONFIG.lonKantor]
         			)
         		.addTo(map)
         		.bindPopup("Lokasi Kantor");
         // Radius kantor
         		L.circle(
         			[CONFIG.latKantor, CONFIG.lonKantor],
         			{
         				radius:CONFIG.radius,
         				color:'green'
         			}
         			).addTo(map);
         	}
         // ======================
         // HITUNG JARAK
         // ======================
         	function hitungJarak(lat,lon){
         		return map.distance(
         			[lat,lon],
         			[CONFIG.latKantor, CONFIG.lonKantor]
         			);
         	}
         // ======================
         // UPDATE LOKASI USER
         // ======================
         	function updateLokasi(position){
         		const lat =
         		position.coords.latitude;
         		const lon =
         		position.coords.longitude;
         		const accuracy =
         		position.coords.accuracy;
         // Set hidden input
         		$('#latitude').val(lat);
         		$('#longitude').val(lon);
         // Remove marker lama
         		if(markerUser){
         			map.removeLayer(markerUser);
         		}
         // Tambah marker baru
         		markerUser =
         		L.marker(
         			[lat,lon],
         			{ icon:redIcon }
         			).addTo(map);
         // Fokus map
         		map.setView([lat,lon],18);
         // Hitung jarak
         		const jarak =
         		hitungJarak(lat,lon);
         // Status lokasi
         		let statusLokasi;
         		if(jarak <= CONFIG.radius){
         			statusLokasi =
         			`<span class="badge bg-success"><i class="bi bi-check-circle"></i> Dalam Radius</span>`;
         			$('#btnAbsen')
         			.prop('disabled',false);
         		}else{
         			statusLokasi =
         			`<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Di Luar Radius</span>`;
         			$('#btnAbsen')
         			.prop('disabled',true);
         		}
         // Update Info
         		$('#info').html(
         			`<i class="bi bi-crosshair2"></i> Akurasi GPS: `
         			+ formatAngka(accuracy)
         			+ " meter<br>"
         			+ `<i class="bi bi-geo-alt-fill"></i> Jarak dari Kantor: `
         			+ formatAngka(jarak)
         			+ " meter<br>"
         			+ `<i class="bi bi-check-square"></i> Status Lokasi Kehadiran: `
         			+ statusLokasi
         			);
         	}
         // ======================
         // ERROR GPS
         // ======================
         	function gpsError(){
         		showError(
         			"Gagal mengambil lokasi GPS"
         			);
         	}
         // ======================
         // GET GPS REALTIME
         // ======================
         	function getLocation(){
         		if(!navigator.geolocation){
         			showError(
         				"Browser tidak mendukung GPS"
         				);
         			return;
         		}
         		navigator.geolocation.watchPosition(
         			updateLokasi,
         			gpsError,
         			{
         				enableHighAccuracy:true,
         				maximumAge:0,
         				timeout:10000
         			}
         			);
         	}
         function formatAngka(angka){
			return new Intl.NumberFormat('id-ID', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
			}).format(angka);
		}
         // ======================
         // PREVIEW FOTO
         // ======================
         	function initPreviewFoto(){
         		$('#foto').on('change',function(){
         			const file =
         			this.files[0];
         			if(!file) return;
         // Validasi ukuran
         			if(file.size > CONFIG.maxFotoSize){
         				showWarning(
         					"Ukuran foto maksimal 2MB"
         					);
         				$(this).val('');
         				$('#previewFoto').hide();
         				return;
         			}
         // Preview gambar
         			const reader =
         			new FileReader();
         			reader.onload = function(e){
         				$('#previewFoto')
         				.attr('src', e.target.result)
         				.show();
         			};
         			reader.readAsDataURL(file);
         		});
         	}
         // ======================
         // VALIDASI FORM
         // ======================
         	function validasiForm(){
         		if(!$('#latitude').val()){
         			showWarning(
         				"Lokasi belum ditemukan"
         				);
         			return false;
         		}
         		if(!$('#foto').val()){
         			showWarning(
         				"Foto wajib diambil"
         				);
         			return false;
         		}
         		return true;
         	}
         // ======================
         // SUBMIT ABSENSI
         // ======================
         	function submitAbsen(){
         		$('#formAbsen').on('submit',function(e){
         			e.preventDefault();
         			if(!validasiForm()) return;
         			const formData =
         			new FormData(this);
         			$.ajax({
         				url:"{{ url('absensi/store') }}",
         				method:"POST",
         				data:formData,
         				processData:false,
         				contentType:false,
         				beforeSend:function(){
         					showLoading();
         				},
         				success:function(res){
         					if(res.status){
         						Swal.fire({
         							icon:'success',
         							title:'Absensi Berhasil',
         							text:res.message,
         							confirmButtonColor:'#0d6efd'
         						}).then(()=>{
         							location.reload();
         						});
         					}else{
         
         						showError(res.message);
         					}
         				},
         				error:function(xhr){
         					showError(
         						"Gagal menyimpan absensi"
         						);
         					console.log(
         						xhr.responseText
         						);
         				}
         			});
         		});
         	}
         // ======================
         // INIT APP
         // ======================
         	function initApp(){
         		initMap();
         		getLocation();
         		initPreviewFoto();
         		submitAbsen();
         	}
         // ======================
         // READY
         // ======================
         	$(document).ready(function(){
         		initApp();
         	});
      </script>
   </body>
</html>