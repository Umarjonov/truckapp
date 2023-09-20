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
                {{--                                make user info and map card--}}
                <div class="container mt-5">
                    <div class="row">
                        <div class="col-12">
                            @foreach($latestTrack as $track)
                                <div class="card p-4 m-3 d-flex justify-between flex-row items-center">
                                    <span class="font-bold">{{$track->type?'ketti':'keldi'}}</span>
                                    <span>{{$track->created_at}}</span>
                                    <button type="button" class="btn btn-primary" data-toggle="modal"
                                            data-map="{{$track->latitude.','.$track->longitude}}"
                                            data-target="#exampleModal">Map
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>


                <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"
                     aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">New message</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form>
                                    <div class="form-group">
                                        <label for="recipient-name" class="col-form-label">Recipient:</label>
                                        <input type="text" class="form-control" id="recipient-name">
                                    </div>
                                    <div class="form-group">
                                        <label for="message-text" class="col-form-label">Message:</label>
                                        <textarea class="form-control" id="message-text"></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary">Send message</button>
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
                <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
                        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
                        crossorigin="anonymous"></script>
                <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
                        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
                        crossorigin="anonymous"></script>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
                        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
                        crossorigin="anonymous"></script>
                <script>

                    // Initialize map
                    // function initMap() {
                    //     var map = new google.maps.Map(document.getElementById('map'), {
                    //         center: {lat: 40.712776, lng: -74.005974}, // Replace with your coordinates
                    //         zoom: 12
                    //     });
                    //
                    //     // You can customize map markers or other features as needed
                    //     var marker = new google.maps.Marker({
                    //         position: {lat: 40.712776, lng: -74.005974}, // Replace with your coordinates
                    //         map: map,
                    //         title: 'Marker'
                    //     });
                    // }
                    $('#exampleModal').on('show.bs.modal', function (event) {
                        var button = $(event.relatedTarget) // Button that triggered the modal
                        var recipient = button.data('map') // Extract info from data-* attributes
                        // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
                        // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
                        var modal = $(this)
                        modal.find('.modal-title').text('New message to ' + recipient)
                        modal.find('.modal-body input').val(recipient)
                    })
                    // Toggle map card visibility
                    // document.querySelector('.map-toggle').addEventListener('click', function () {
                    //     document.querySelector('#mapCard').classList.toggle('show');
                    // });
                </script>
                {{--                                end make user info and map card--}}
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
