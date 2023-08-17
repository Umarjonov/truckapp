<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __("Foydalanuvchi ma'lumotlari va Joylashuv") }}
            </h2>
            <span>{{ $user->name }}</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-3">
                <div class="mb-3">
                    <a href="{{ route('admin.index') }}">Orqage</a>
                </div>
                {{--                make user info and map card--}}
                <div class="container mt-5">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Contact Information</h5>
                                    <p>Name: John Doe</p>
                                    <p>Email: johndoe@example.com</p>
                                    <p>Phone: +1 123-456-7890</p>
                                    <button class="btn btn-primary map-toggle">
                                        <i class="fa fa-map-marker"></i> Show Map
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card collapse" id="mapCard">
                                <div class="card-body">
                                    <h5 class="card-title">User Location</h5>
                                    <div id="map" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Google Maps API script -->
                <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async
                        defer></script>

                <!-- Add Bootstrap JS and FontAwesome links -->
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
                <script src="https://kit.fontawesome.com/a076d05399.js"></script>

                <script>
                    // Initialize map
                    function initMap() {
                        var map = new google.maps.Map(document.getElementById('map'), {
                            center: {lat: 40.712776, lng: -74.005974}, // Replace with your coordinates
                            zoom: 12
                        });

                        // You can customize map markers or other features as needed
                        var marker = new google.maps.Marker({
                            position: {lat: 40.712776, lng: -74.005974}, // Replace with your coordinates
                            map: map,
                            title: 'Marker'
                        });
                    }

                    // Toggle map card visibility
                    document.querySelector('.map-toggle').addEventListener('click', function () {
                        document.querySelector('#mapCard').classList.toggle('show');
                    });
                </script>
                {{--                end make user info and map card--}}
                <table class="table table-bordered">

                </table>

            </div>
        </div>
    </div>
</x-app-layout>
{{--            style --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f8f9fa;
    }

    .card {
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .card-title {
        color: #007bff;
    }

    .btn-primary.map-toggle {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary.map-toggle:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }
</style>
{{--            endstyle --}}
