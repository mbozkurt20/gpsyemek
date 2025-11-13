"use strict";

function readURL(input, previewImage) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#' + previewImage).attr('src', e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Dosya adı göstermek için
$(".custom-file-input").on("change", function() {
    let fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
});

// Summernote init
if (jQuery().summernote) {
    $(".summernote").summernote({
        dialogsInBody: true,
        minHeight: 250,
    });
    $(".summernote-simple").summernote({
        dialogsInBody: true,
        minHeight: 150,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough']],
            ['para', ['paragraph']]
        ]
    });
}

// Timepicker init
if (jQuery().timepicker && $(".timepicker").length) {
    $(".timepicker").timepicker({
        icons: {
            up: 'fas fa-chevron-up',
            down: 'fas fa-chevron-down'
        }
    });
}

let map, marker, geocoder, infoWindow;

async function initMap() {
    geocoder = new google.maps.Geocoder();

    // Konum alma
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                initializeMap(position.coords.latitude, position.coords.longitude);
            },
            function () {
                console.log('Konum izni verilmedi. Varsayılan konum kullanılacak.');
                initializeMap(41.0082, 28.9784); // Varsayılan: İstanbul
            }
        );
    } else {
        alert("Tarayıcınız konum özelliğini desteklemiyor.");
    }
}

function initializeMap(lat, lng) {
    const myLatlng = { lat: lat, lng: lng };

    map = new google.maps.Map(document.getElementById("googleMap"), {
        zoom: 15,
        center: myLatlng,
    });

    infoWindow = new google.maps.InfoWindow({
        content: "Haritaya tıklayarak konum seçebilirsiniz.",
        position: myLatlng,
    });
    infoWindow.open(map);

    marker = new google.maps.Marker({
        position: myLatlng,
        map,
        draggable: true,
        title: "Seçilen konum",
    });

    // Input alanlarını güncelle
    $('#lat').val(lat);
    $('#long').val(lng);

    // Haritaya tıklayınca marker güncelle
    map.addListener("click", (mapsMouseEvent) => {
        infoWindow.close();
        const clickedLatLng = mapsMouseEvent.latLng.toJSON();
        updateMarker(clickedLatLng.lat, clickedLatLng.lng);
    });

    // Marker sürüklenince inputları güncelle
    marker.addListener("dragend", function (event) {
        $('#lat').val(event.latLng.lat());
        $('#long').val(event.latLng.lng());
    });

    // “Haritada Göster” butonuna tıklama olayı
    $('#show-on-map').on('click', function() {
        const address = $('#address-input').val().trim();
        if (!address) {
            alert("Lütfen bir adres giriniz.");
            return;
        }
        geocodeAddress(address);
    });
}

function updateMarker(lat, lng) {
    const latlng = new google.maps.LatLng(lat, lng);
    marker.setPosition(latlng);
    map.setCenter(latlng);
    $('#lat').val(lat);
    $('#long').val(lng);
}

function geocodeAddress(address) {
    geocoder.geocode({ address: address }, function(results, status) {
        if (status === "OK") {
            const location = results[0].geometry.location;
            updateMarker(location.lat(), location.lng());
            map.setZoom(15);
            infoWindow.setContent(results[0].formatted_address);
            infoWindow.open(map, marker);
        } else {
            alert("Adres bulunamadı: " + status);
        }
    });
}

$(document).ready(function() {
    $('.select2').select2();
});
