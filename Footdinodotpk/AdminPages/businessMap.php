<?php
require('admin.top.inc.php');
$userid=$_SESSION['USERID'];
$user=$_SESSION['USERNAME'];

$query = "SELECT * FROM businessvendordetail WHERE 1";
$result = mysqli_query($con, $query);

$xml = "<?xml version='1.0' ?>\n";
$xml = $xml . "<markers>";
$xml = $xml . "\n\t";
$ind = 0;
// Iterate through the rows, printing XML nodes for each
while ($row = @mysqli_fetch_assoc($result)) {
    // Add to XML document node
    $xml = $xml . '<marker ';
    $xml = $xml . 'id="' . $row['id'] . '" ';
    $xml = $xml . 'name="' . parseToXML($row['businessName']) . '" ';
    $xml = $xml . 'address="' . parseToXML($row['address']) . '" ';
    $xml = $xml . 'lat="' . $row['latitude'] . '" ';
    $xml = $xml . 'lng="' . $row['longitude'] . '" ';
    $xml = $xml . 'type="' . $row['vertical'] . '" ';
    $xml = $xml . "/>";
    $xml = $xml . "\n\t";
    $ind = $ind + 1;
}

if(array_key_exists('lat',$_SESSION) && array_key_exists('long',$_SESSION)) {
    $lat = $_SESSION['lat'];
    $long = $_SESSION['long'];
    $xml = $xml . "\n";
    $xml = $xml . '<marker ';
    $xml = $xml . 'id="' . $userid . '" ';
    $xml = $xml . 'name="' . $user . '" ';
    $xml = $xml . 'address="self" ';
    $xml = $xml . 'lat="' . $lat . '" ';
    $xml = $xml . 'lng="' . $long . '" ';
    $xml = $xml . 'type="self" ';
    $xml = $xml . "/>";
    $xml = $xml . "\n\t";
}
$xml=$xml."</markers>";

$myfile = fopen("vendors.xml", "w") or die("Unable to open file!");
fwrite($myfile, $xml);
fclose($myfile);
?>

<!DOCTYPE html >
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no"/>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>

    <style>
        /* Always set the map height explicitly to define the size of the div
         * element that contains the map. */
        #map {
            height: 100%;
        }

        /* Optional: Makes the sample page fill the window. */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>
    <title></title>
</head>

<body>
<div id="map"></div>
<p id="m" hidden></p>
<p id="n" hidden></p>
<script>

    var x = document.getElementById("m");
    var y = document.getElementById("n");

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        } else {
            x.innerHTML = "Geolocation is not supported by this browser.";
        }
    }

    function showPosition(position) {
        x.innerHTML = "" + position.coords.latitude;
        y.innerHTML= "" + position.coords.longitude;
    }

    getLocation();

    var customLabel = {
        Restaurant: {
            label: 'R'
        },
        Pakwancenter: {
            label: 'P'
        },
        self: {
            label: 'You'
        }
    };

    function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
            center: new google.maps.LatLng(24.920156, 67.1186785),
            zoom: 16
        });
        var infoWindow = new google.maps.InfoWindow;
        downloadUrl('vendors.xml', function (data) {
            var xml = data.responseXML;
            var xml2=new XMLSerializer().serializeToString(xml.documentElement);
            // xml2+='<markers>latitude="'+x.innerHTML+'" longitude="'+y.innerHTML+'" type="self" </markers>';
            // $(xml2).append('<br><marker type="self"<br>');
            // window.alert(xml2);
            var markers = xml.documentElement.getElementsByTagName('marker');
            var uid='<?php echo $userid; ?>';

            Array.prototype.forEach.call(markers, function (markerElem) {
                var id = markerElem.getAttribute('id');
                var name = markerElem.getAttribute('name');
                var address = markerElem.getAttribute('address');
                var type = markerElem.getAttribute('type');
                if(id===uid || type==='Pakwancenter' || type==='Restaurant') {

                    var point = new google.maps.LatLng(
                        parseFloat(markerElem.getAttribute('lat')),
                        parseFloat(markerElem.getAttribute('lng')));

                    var infowincontent = document.createElement('div');

                    var strong = document.createElement('strong');
                    strong.textContent = name
                    infowincontent.appendChild(strong);
                    infowincontent.appendChild(document.createElement('br'));

                    var text = document.createElement('text');
                    text.textContent = address
                    infowincontent.appendChild(text);

                    infowincontent.appendChild(document.createElement('br'));

                    // var bid = document.createElement('bid');
                    // bid.textContent = id
                    // infowincontent.appendChild(bid);

                    var icon = customLabel[type] || {};
                    var marker = new google.maps.Marker({
                        map: map,
                        position: point,
                        label: icon.label
                    });

                    marker.addListener('click', function () {
                        infoWindow.setContent(infowincontent);
                        infoWindow.open(map, marker);
                    });
                }
            });
        });
    }


    function downloadUrl(url, callback) {
        var request = window.ActiveXObject ?
            new ActiveXObject('Microsoft.XMLHTTP') :
            new XMLHttpRequest;

        request.onreadystatechange = function () {
            if (request.readyState === 4) {
                request.onreadystatechange = doNothing;
                callback(request, request.status);
            }
        };

        request.open('GET', url, true);
        request.send(null);
    }

    function doNothing() {
    }
</script>
<script async
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCZB8NjctqwKX6ym5fUdINg3YP2ItsiAc4&callback=initMap">
</script>
</body>