<?php
/*
This file is part of VCMS.

VCMS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

VCMS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with VCMS. If not, see <http://www.gnu.org/licenses/>.
*/

// Hilfsfunktion: Serverseitiges Nominatim Geocoding (muss vor erster Nutzung definiert sein)
if(!function_exists('vcms_server_geocode')){
	function vcms_server_geocode($address, $contactEmail){
		$ua = 'VCMS-Server-Geocoder/1.0 (contact: '.preg_replace('/[^a-z0-9_@\-\.]/i','',$contactEmail).')';
		$url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($address);
		if(!function_exists('curl_init')){ return [null,null,'error_nocurl','',0]; }
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'Accept: application/json' ]);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$resp = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($resp === false){ $err = curl_error($ch); }
		curl_close($ch);
		if($code !== 200 || !$resp){
			return [null, null, 'error', $resp?:($err??''), $code];
		}
		$data = json_decode($resp, true);
		if(is_array($data) && isset($data[0]['lat']) && isset($data[0]['lon'])){
			return [ (float)$data[0]['lat'], (float)$data[0]['lon'], 'ok', $resp, $code ];
		}
		return [ null, null, 'notfound', $resp, $code ];
	}
}

// Zugriffsschutz: nur eingeloggte Nutzer
if(!isset($libAuth) || !is_object($libAuth) || !method_exists($libAuth, 'isLoggedin') || !$libAuth->isLoggedin())
	exit();

// Erwarte zentrale VCMS-Objekte; bei fehlendem Kontext abbrechen (und Hilfszuweisungen für Linter)
if(!isset($libDb) || !is_object($libDb)) { exit(); }
if(!isset($libPerson) || !is_object($libPerson)) { exit(); }
if(!isset($libConfig) || !is_object($libConfig)) { $libConfig = (object)[]; }
if(!isset($libString)) { $libString = null; }

// Optional: Auto-Geocoding deaktivieren per URL-Parameter ?geocode=0 (Standard: aktiv)
$autoGeocode = true;
$maxPerRequest = 10; // global definieren, damit Warnbox darauf zugreifen kann
if(isset($_GET['geocode'])){
	$gv = strtolower(trim((string)$_GET['geocode']));
	if($gv === '0' || $gv === 'false' || $gv === 'no'){
		$autoGeocode = false;
	}
}

// Sicherstellen, dass die Geocode-Tabelle existiert
try {
	$libDb->prepare("CREATE TABLE IF NOT EXISTS base_geodaten (
		id INT AUTO_INCREMENT PRIMARY KEY,
		address VARCHAR(512) NOT NULL UNIQUE,
		lat DOUBLE NULL,
		lon DOUBLE NULL,
		status VARCHAR(32) DEFAULT NULL,
		raw_json MEDIUMTEXT NULL,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at TIMESTAMP NULL DEFAULT NULL,
		INDEX(status)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8")->execute();
} catch(Exception $e){ /* Debugging entfernt */ }

// Prüfen ob Tabelle wirklich existiert
$geoTableAvailable = false;
try {
	$chk = $libDb->prepare("SHOW TABLES LIKE 'base_geodaten'");
	if($chk->execute() && $chk->fetch()) { $geoTableAvailable = true; }
} catch(Exception $e){ /* Debugging entfernt */ }

// Sammle Basisdaten der Personen inkl. Adressen
$stmt = $libDb->prepare("SELECT id, anrede, titel, rang, vorname, praefix, name, suffix, gruppe,\n    zusatz1, strasse1, ort1, plz1, land1,\n    zusatz2, strasse2, ort2, plz2, land2\n    FROM base_person\n    WHERE gruppe != 'X' AND gruppe != 'T' AND gruppe != 'C'\n");
$stmt->execute();

$members = [];
$addresses = []; // unique address string => true
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $id = (int)$row['id'];

    // Anzeigename über Helper
    $displayName = $libPerson->getNameString($id, 0);

    // Bevorzugte Adresse wählen (1 vor 2)
    $addrParts1 = [];
    if(!empty($row['strasse1'])) $addrParts1[] = $row['strasse1'];
    $plz1 = isset($row['plz1']) ? $row['plz1'] : '';
    $ort1 = isset($row['ort1']) ? $row['ort1'] : '';
    $cityLine1 = trim($plz1.' '.$ort1);
    if(!empty($cityLine1)) $addrParts1[] = $cityLine1;
    if(!empty($row['land1'])) $addrParts1[] = $row['land1'];
    $address1 = trim(implode(', ', array_filter($addrParts1)));

    $addrParts2 = [];
    if(!empty($row['strasse2'])) $addrParts2[] = $row['strasse2'];
    $plz2 = isset($row['plz2']) ? $row['plz2'] : '';
    $ort2 = isset($row['ort2']) ? $row['ort2'] : '';
    $cityLine2 = trim($plz2.' '.$ort2);
    if(!empty($cityLine2)) $addrParts2[] = $cityLine2;
    if(!empty($row['land2'])) $addrParts2[] = $row['land2'];
    $address2 = trim(implode(', ', array_filter($addrParts2)));

    $chosenAddress = $address1 !== '' ? $address1 : $address2;
    if($chosenAddress === ''){
        continue; // Ohne Adresse nicht anzeigen
    }

    $members[] = [
        'id' => $id,
        'name' => $displayName,
        'group' => $row['gruppe'],
        'address' => $chosenAddress,
        'lat' => null,
        'lon' => null
    ];
    $addresses[$chosenAddress] = true;
}

$addressList = array_keys($addresses);
$geocodeInfo = [ 'total_addresses' => count($addressList), 'cached' => 0, 'missing' => 0, 'geocoded_now' => 0, 'errors' => [] ];
$addressCoords = [];

if(!empty($addressList) && $geoTableAvailable){
	// Bereits gecachte Geodaten holen
	$chunks = array_chunk($addressList, 1000); // Sicherheits Chunking
	foreach($chunks as $chunk){
		$in = implode(',', array_fill(0, count($chunk), '?'));
		$select = $libDb->prepare("SELECT address, lat, lon, status FROM base_geodaten WHERE address IN ($in)");
		foreach($chunk as $i => $addr){
			$select->bindValue($i+1, $addr, PDO::PARAM_STR);
		}
		$select->execute();
		while($r = $select->fetch(PDO::FETCH_ASSOC)){
			if($r['lat'] !== null && $r['lon'] !== null && $r['status'] === 'ok'){
				$addressCoords[$r['address']] = [ 'lat' => (float)$r['lat'], 'lon' => (float)$r['lon'] ];
			}
		}
	}
	$geocodeInfo['cached'] = count($addressCoords);

	// Fehlende Adressen bestimmen
	$missingAddresses = array_values(array_diff($addressList, array_keys($addressCoords)));
	$geocodeInfo['missing'] = count($missingAddresses);

	// Serverseitiges Geocoding (limit pro Request um Laufzeit & Rate-Limit zu schonen)
	if($autoGeocode && $geocodeInfo['missing'] > 0 && function_exists('curl_init')){
		$toGeocodeNow = array_slice($missingAddresses, 0, $maxPerRequest);
		foreach($toGeocodeNow as $i => $addr){
			$geoRes = vcms_server_geocode($addr, isset($libConfig->adminMail) ? $libConfig->adminMail : 'admin@example.com');
			list($lat, $lon, $status, $raw, $httpCode) = $geoRes;
			try {
				$insert = $libDb->prepare("INSERT INTO base_geodaten (address, lat, lon, status, raw_json, updated_at) VALUES (:a,:lat,:lon,:s,:raw, NOW()) ON DUPLICATE KEY UPDATE lat=VALUES(lat), lon=VALUES(lon), status=VALUES(status), raw_json=VALUES(raw_json), updated_at=VALUES(updated_at)");
				$insert->bindValue(':a', $addr, PDO::PARAM_STR);
				if($lat === null || $lon === null){
					$insert->bindValue(':lat', null, PDO::PARAM_NULL);
					$insert->bindValue(':lon', null, PDO::PARAM_NULL);
				} else {
					$insert->bindValue(':lat', $lat);
					$insert->bindValue(':lon', $lon);
				}
				$insert->bindValue(':s', $status, PDO::PARAM_STR);
				$insert->bindValue(':raw', $raw, PDO::PARAM_STR);
				$insert->execute();
				if($status === 'ok' && $lat !== null && $lon !== null){
					$addressCoords[$addr] = [ 'lat' => $lat, 'lon' => $lon ];
				}
			} catch(Exception $e){ /* Debugging entfernt */ }
			// Nominatim Rate Limit: 1s Pause zwischen Requests
			if($i < count($toGeocodeNow)-1){
				usleep(1000000); // 1 Sekunde
			}
		}
		$geocodeInfo['geocoded_now'] = count($toGeocodeNow);
	}
} elseif(!$geoTableAvailable) {
	if($debugGeo){ echo '<div class="alert alert-danger">Hinweis: Tabelle base_geodaten existiert nicht – Caching/Geocoding deaktiviert.</div>'; }
}

// Koordinaten in Members übernehmen
foreach($members as &$m){
	$addr = $m['address'];
	if(isset($addressCoords[$addr])){
		$m['lat'] = $addressCoords[$addr]['lat'];
		$m['lon'] = $addressCoords[$addr]['lon'];
	}
}
unset($m);

// Seite ausgeben
$titlePrefix = (isset($libConfig) && isset($libConfig->verbindungName)) ? $libConfig->verbindungName : 'VCMS';
echo '<h1>' .$titlePrefix. ' - Mitgliederkarte</h1>';

echo isset($libString) ? $libString->getErrorBoxText() : '';
echo isset($libString) ? $libString->getNotificationBoxText() : '';

// Hinweistext (aktualisiert)
echo '<div class="alert alert-info">';
echo 'Diese Karte nutzt OpenStreetMap (OSM). Adressen werden serverseitig per Nominatim (mit Caching in <code>base_geodaten</code>) geokodiert. Pro Seitenaufruf werden maximal 10 fehlende Adressen angefragt, um das Rate-Limit zu schonen.';
$stats = sprintf(' Adressen: %d, im Cache: %d, fehlend: %d%s%s',
	$geocodeInfo['total_addresses'],
	$geocodeInfo['cached'],
	$geocodeInfo['missing'],
	$geocodeInfo['geocoded_now']>0 ? ', neu geokodiert: '.$geocodeInfo['geocoded_now'] : '',
	$debugGeo && !empty($geocodeInfo['errors']) ? ', Fehler: '.implode(',', array_slice($geocodeInfo['errors'],0,5)) : ''
);
echo '<br/><small>'.$stats.'</small>';
echo '</div>';

// Kartencontainer
echo '<div id="member-map" style="height: 70vh; width: 100%; border: 1px solid #ddd; border-radius: 4px;"></div>';

// Hinweis falls noch keine Marker vorhanden
if($autoGeocode && $geocodeInfo['missing'] > 0 && count($addressCoords) === 0){
	echo '<div class="alert alert-warning" style="margin-top:8px;">Es sind noch keine Geokoordinaten im Cache. Pro Seitenaufruf werden bis zu '.$maxPerRequest.' Adressen geokodiert. Bitte die Seite nach einigen Sekunden neu laden.</div>';
} elseif(!$autoGeocode && count($addressCoords) === 0){
	echo '<div class="alert alert-warning" style="margin-top:8px;">Automatisches Geocoding ist deaktiviert und es liegen noch keine gecachten Geodaten vor. Aktivieren Sie das Geocoding, um Koordinaten zu erzeugen.</div>';
}

// Steuerleiste
if(!$autoGeocode){
	echo '<p class="mt-2"><a class="btn btn-primary" href="index.php?pid=intranet_mitglied_karte">Geocoding aktivieren</a> ';
	echo '<a class="btn btn-default" href="index.php?pid=intranet_mitglied_karte&amp;geocode=0">Geocoding auslassen</a></p>';
} elseif($geocodeInfo['missing'] > 0){
	echo '<p class="mt-2">';
	echo '<a class="btn btn-default" href="index.php?pid=intranet_mitglied_karte&amp;geocode=0">Automatisches Geocoding pausieren</a> ';
	echo '<a class="btn btn-default" href="index.php?pid=intranet_mitglied_karte&amp;debuggeo=1">Debug anzeigen</a>';
	echo '</p>';
} elseif(!$debugGeo) {
	echo '<p class="mt-2"><a class="btn btn-default" href="index.php?pid=intranet_mitglied_karte&amp;debuggeo=1">Debug anzeigen</a></p>';
}

// Daten als JSON einbetten – nur Einträge mit Koordinaten erscheinen als Marker
$membersJson = json_encode($members, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
?>
<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
  crossorigin=""/>
<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"
/>
<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css"
/>
<script
  src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
  integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
  crossorigin="">
</script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
(function(){
  var MEMBERS = <?php echo $membersJson ?: '[]'; ?>;

  // Adressen zu Mitgliedern gruppieren (nur mit Koordinaten)
  var addressGroups = new Map();
  MEMBERS.forEach(function(m){
    if(m.lat == null || m.lon == null) return; // Nur vollständig
    var key = (m.address || '').trim();
    if(!key) return;
    if(!addressGroups.has(key)) addressGroups.set(key, {address: key, lat: m.lat, lon: m.lon, members: []});
    addressGroups.get(key).members.push(m);
  });

  var map = L.map('member-map', { scrollWheelZoom: true }).setView([51.1657, 10.4515], 5);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> Mitwirkende'
  }).addTo(map);

  var cluster = L.markerClusterGroup({
    showCoverageOnHover: false,
    spiderfyOnMaxZoom: true,
    maxClusterRadius: 60
  });

  addressGroups.forEach(function(entry){
    var html = entry.members.map(function(m){
      var url = 'index.php?pid=intranet_person&id=' + encodeURIComponent(m.id);
      return '<div class="mb-2">' +
        '<a href="' + url + '"><strong>' + escapeHtml(m.name) + '</strong></a>' +
        '<br/><small>' + escapeHtml(entry.address) + '</small>' +
        '</div>';
    }).join('');

    var marker = L.marker([entry.lat, entry.lon]);
    marker.bindPopup(html, { maxWidth: 320 });
    cluster.addLayer(marker);
  });

  map.addLayer(cluster);
  var bounds = cluster.getBounds();
  if(bounds && bounds.isValid()){
    map.fitBounds(bounds.pad(0.1));
  }

  function escapeHtml(str){
    return String(str)
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/\"/g,'&quot;')
      .replace(/'/g,'&#039;');
  }
})();
</script>
