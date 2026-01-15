<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Play Amtgard!</title>
  <link rel="shortcut icon" href="https://ork.amtgard.com/favicon.ico">

  <!-- CSS  -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!-- Compiled and minified CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

  <!-- Compiled and minified JavaScript -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.3.1.min.js"
    integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.js"></script>
  <script>
    function playAmtgard(call, latitude, longitude, start, end, distance, limit, process) {
      $.getJSON("https://ork.amtgard.com/orkservice/Json/index.php?request=",
        {
          call: call,
          latitude: latitude,
          longitude: longitude,
          start: start,
          end: end,
          distance: distance,
          limit: limit,
          request: {}
        },
        function (data) {
          process(data);
        });
    }

    function playAmtgardEvents(latitude, longitude, start, end, distance, limit) {
      playAmtgard("Event/PlayAmtgard0", latitude, longitude, start, end, distance, limit, function (data) {
        $('#event-days tbody').html('');
        $.each(data.ParkDays, function (i, e) {
          var Location = '';
          if (e.UnitName) Location = e.UnitName;
          else if (e.ParkName) Location = '<a href="https://ork.amtgard.com/orkui/index.php?Route=Kingdom/index/' + e.KingdomId + '">' + e.KingdomName + '</a>: <a href="https://ork.amtgard.com/orkui/index.php?Route=Park/index/' + e.ParkId + '">' + e.ParkName + '</a>';
          else if (e.KingdomName) Location = '<a href="https://ork.amtgard.com/orkui/index.php?Route=Kingdom/index/' + e.KingdomId + '">' + e.KingdomName + '</a>';
          $('#event-days tbody').append(
            '<tr href="http://maps.google.com/maps?z=14&t=m&q=loc:' + e.Latitude + ',' + e.Longitude + '">' +
            '<td class="long-col">' + Location + '</td>' +
            '<td><a href="https://ork.amtgard.com/orkui/index.php?Route=Event/index/' + e.EventId + '">' + e.EventName + '</a></td>' +
            '<td>' + moment(e.Start, 'YYYY-MM-DD HH:mm:ss').format('dddd, MMM Do h:mm A') + '</td>' +
            '<td class="long-col"><a href="http://maps.google.com/maps?z=14&t=m&q=loc:' + e.Latitude + ',' + e.Longitude + '">' + e.Address + '</a></td>' +
            '</tr>'
          );
        });
      });
    }

    function playAmtgardParks(latitude, longitude, start, end, distance, limit) {
      playAmtgard("Park/PlayAmtgard0", latitude, longitude, start, end, distance, limit, function (data) {
        $('#park-days tbody').html('');

        // Initialize map if Google Maps API is loaded
        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
          console.log("Google Maps API loaded, initializing map...");
          initMap(latitude, longitude);
          clearMarkers();
          var bounds = new google.maps.LatLngBounds();

          if (!window.infoWindow) {
            window.infoWindow = new google.maps.InfoWindow();

            // Close InfoWindow on map click
            google.maps.event.addListener(map, "click", function () {
              window.infoWindow.close();
            });

            // Close InfoWindow on Esc key
            document.addEventListener('keydown', function (event) {
              if (event.key === "Escape") {
                window.infoWindow.close();
              }
            });
          }
        } else {
          console.error("Google Maps API NOT loaded.");
        }

        $.each(data.ParkDays, function (i, e) {
          // Add marker to map
          if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
            var marker = new google.maps.Marker({
              position: { lat: e.Latitude, lng: e.Longitude },
              map: map,
              title: e.ParkName
            });

            var contentString = '<div>' +
              '<p><b>Next Meeting:</b> ' + moment(e.NextDay + ' ' + e.Time, 'YYYY-MM-DD HH:mm:ss').format('dddd, MMM Do h:mm A') + '</p>' +
              '<p><a href="http://maps.google.com/maps?z=14&t=m&q=loc:' + e.Latitude + ',' + e.Longitude + '" target="_blank">Get Directions</a></p>' +
              '</div>';

            marker.addListener("click", function () {
              window.infoWindow.setOptions({ headerContent: e.ParkName });
              window.infoWindow.setContent(contentString);
              window.infoWindow.open(map, marker);
            });

            markers.push(marker);
            bounds.extend(marker.getPosition());
          }

          $('#park-days tbody').append(
            '<tr href="http://maps.google.com/maps?z=14&t=m&q=loc:' + e.Latitude + ',' + e.Longitude + '">' +
            '<td class="long-col"><a href="https://ork.amtgard.com/orkui/index.php?Route=Kingdom/index/' + e.KingdomId + '">' + e.KingdomName + '</a></td>' +
            '<td><a href="https://ork.amtgard.com/orkui/index.php?Route=Park/index/' + e.ParkId + '">' + e.ParkName + '</a></td>' +
            '<td class="long-col">' + e.Purpose + '</td>' +
            '<td>' + moment(e.NextDay + ' ' + e.Time, 'YYYY-MM-DD HH:mm:ss').format('dddd, MMM Do h:mm A') + '</td>' +
            '<td class="long-col"><a href="http://maps.google.com/maps?z=14&t=m&q=loc:' + e.Latitude + ',' + e.Longitude + '">' + e.Address + '</a></td>' +
            '</tr>'
          );
        });

        // Fit bounds if markers exist
        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && markers.length > 0) {
          map.fitBounds(bounds);
        }
      });
    }

    function geocode(address, process) {
      $.getJSON("https://ork.amtgard.com/orkservice/Json/index.php?request=&city=&state=&postal_code=",
        {
          call: "Map/Geocode0",
          address: address
        },
        function (data) {
          process(data);
        });
    }

    $(document).ready(function () {
      function runSearch() {
        geocode($('#look-near').val(), function (geocode) {
          //playAmtgardEvents(geocode.Result.Location.location.lat, geocode.Result.Location.location.lng, '<?= date("Y-m-d") ?>', '<?= date("Y-m-d", strtotime("+6 month")) ?>', 500, 12);
          playAmtgardParks(geocode.Result.Location.location.lat, geocode.Result.Location.location.lng, '<?= date("Y-m-d") ?>', '<?= date("Y-m-d", strtotime("+1 week")) ?>', 100, 12);
        });
      }

      $('#play-amtgard').on('click', function (e) {
        runSearch();
      });

      $('#look-near').on('blur', function () {
        runSearch();
      });

      $('#look-near').on('keypress', function (e) {
        if (e.which == 13) {
          $(this).blur();
        }
      });

      $('table').on('click', 'tbody>tr', function (e) {
        window.location.href = $(this).attr('href');
      });

      <?php if (isset($_GET['near']) && strlen(trim($_GET['near'])) > 0): ?>
        geocode('<?= $_GET['near'] ?>', function (geocode) {
          //playAmtgardEvents(geocode.Result.Location.location.lat, geocode.Result.Location.location.lng, '<?= date("Y-m-d") ?>', '<?= date("Y-m-d", strtotime("+6 month")) ?>', 500, 12);
          playAmtgardParks(geocode.Result.Location.location.lat, geocode.Result.Location.location.lng, '<?= date("Y-m-d") ?>', '<?= date("Y-m-d", strtotime("+1 week")) ?>', 100, 12);
        });
      <?php else: ?>
        navigator.geolocation.getCurrentPosition(function (position) {
          //playAmtgardEvents(position.coords.latitude, position.coords.longitude, '<?= date("Y-m-d") ?>', '<?= date("Y-m-d", strtotime("+6 month")) ?>', 500, 12);
          playAmtgardParks(position.coords.latitude, position.coords.longitude, '<?= date("Y-m-d") ?>', '<?= date("Y-m-d", strtotime("+1 week")) ?>', 100, 12);
        });
      <?php endif; ?>
    });

  </script>
  <script>
    var map;
    var markers = [];

    function initMap(lat, lng) {
      if (!map) {
        map = new google.maps.Map(document.getElementById('map'), {
          center: { lat: lat, lng: lng },
          zoom: 8,
          gestureHandling: 'greedy'
        });
      } else {
        map.setCenter({ lat: lat, lng: lng });
      }
    }

    function clearMarkers() {
      for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
      }
      markers = [];
    }
  </script>
  <script
    src="//maps.googleapis.com/maps/api/js?key=AIzaSyBmD6akS8ZoDDvyNJ2_3dfhXcESpUqRD5g&loading=async&callback=initMapAsync&v=weekly&libraries=marker"
    async defer></script>
  <script>
    function initMapAsync() {
      // Placeholder callback if needed, though we init manually on search
    }
  </script>
  <style>
    @media only screen and (max-width: 768px) {
      .long-col {
        display: none;
      }
    }

    tbody>tr {
      cursor: pointer;
    }

    h1,
    h2,
    h3,
    h4 {
      color: rgb(244, 67, 54);
      font-weight: bold;
    }

    h1 {
      font-size: 2em;
    }

    h2 {
      font-size: 1.8em;
    }

    h3 {
      font-size: 1.5em;
    }

    h4 {
      font-size: 1.2em;
    }

    #map {
      height: 400px;
      width: 100%;
      margin-bottom: 20px;
    }

    @media only screen and (max-width: 600px) {
      #map {
        position: relative;
        left: 50%;
        right: 50%;
        margin-left: -50vw;
        margin-right: -50vw;
        width: 100vw;
        border-radius: 0;
      }
    }
  </style>

  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-VW9QBGS259"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());

    gtag('config', 'G-VW9QBGS259');
  </script>
</head>

<body>
  <div class="flex-container">
    <div class="container">
      <div class="section">

        <div class="row">
          <div class="col s12 m12 l12">
            <h1>Play Amtgard!</h1>
          </div>
          <div class="input-field col m6 s12">
            <input placeholder="Look Near" name="look-near" id="look-near" type="text" class="validate"
              value="<?= isset($_GET['near']) ? $_GET['near'] : '' ?>">
            <label for="look-near">Look Near</label>
          </div>
          <div class="input-field col m6 s12">
            <button class="btn waves-effect waves-light red" type="submit" name="action" id='play-amtgard'>Find
              <i class="material-icons right">send</i>
            </button>
          </div>
          <div class="col s12 m12 l12">
            <h2>Parks</h2>
            <div id="map"></div>
            Within 100 miles and the next week.
            <table id='park-days'>
              <thead>
                <tr>
                  <td class="long-col">Kingdom</td>
                  <td>Park</td>
                  <td class="long-col">Purpose</td>
                  <td>When</td>
                  <td class='long-col'>Address</td>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
            <!--
            <h2>Events</h2>
            Within 500 miles and the next 6 months.
            <table id='event-days'>
              <thead>
                <tr>
                  <td class="long-col">Location</td>
                  <td>Event</td>
                  <td>When</td>
                  <td class='long-col'>Address</td>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          -->
          </div>
        </div>
      </div>
    </div>
  </div>

</body>

</html>