<?php
namespace App;

// CONF
class Config {
    const XML_FILE = './import/unesco.xml';
    const FIRST_NAME_FILE = './import/first-names.txt';
    const LAST_NAME_FILE = './import/last-names.txt';
    const DB_FILE = '../../identifier.sqlite';
}

// let's see if there's anything that user want from us
$get = null;
if(isset($_GET['action'])) {
    $get = $_GET['action'];
}

class SQL {
    private $pdo;

    public function __construct() {
        $this->pdo = new \PDO("sqlite:" . Config::DB_FILE);
    }

    // function rebuilds sqlite database with new random data
    public function rewrite($data) {
        // Clear database
        $this->pdo->exec('DELETE FROM agents');

        // Prepare SQL statement
        $stmt = $this->pdo->prepare('INSERT INTO agents (id, first_name, last_name, latitude, longitude) VALUES (?, ?, ?, ?, ?)');
        foreach ($data as $agent) {
            // Insert everything into the database
            $stmt->execute([$agent['id'], $agent['name'], $agent['surname'], $agent['location']['lat'], $agent['location']['long']]);
        }
    }

    // Get everything from the database
    public function getAll() {
        $stmt = $this->pdo->query('SELECT * FROM agents ORDER BY id ASC');
        return $stmt->fetchAll();
    }
}

class Build {
    private $agents = [];
    private $out = [];

    // Build our data
    public function buildData() {
        // Load an XML with coordinates and ids
        $xml = simplexml_load_file(Config::XML_FILE);

        // Load two text files for names and surnames
        $fn = file(Config::FIRST_NAME_FILE, FILE_IGNORE_NEW_LINES);
        $ln = file(Config::LAST_NAME_FILE, FILE_IGNORE_NEW_LINES);

        // Populate agents
        foreach ($xml->row as $i => $row) {
            $this->agents[$i]['id'] = $i;
            $this->agents[$i]['name'] = $fn[array_rand($fn)];
            $this->agents[$i]['surname'] = $ln[array_rand($ln)];
            $this->agents[$i]['location'] = [
                'lat' => $row->latitude->__toString(),
                'long' => $row->longitude->__toString()
            ];
        }

        // Rewrite database with our new data
        $sql = new SQL();
        $sql->rewrite($this->agents);

        // API output
        $this->out['error'] = '';
        $this->out['sql']['connected'] = true;
        $this->out['sql']['rebuild'] = true;
        $this->out['result'] = [];

        return $this->out;
    }

    // Build API and calculate distance
    public function processDistance($lat, $lon) {
        // Get all of our data from database
        $sql = new SQL();
        $sql_agents = $sql->getAll();

        // If our database is empty - rebuild it
        if(sizeof($sql_agents) == 0) {
            $build = new Build();
            $build->buildData();
        }

        // Build our API results output
        foreach($sql_agents as $agent) {
            $this->out['result'][$agent['id']]['id'] = $agent['id'];
            $this->out['result'][$agent['id']]['first_name'] = $agent['first_name'];
            $this->out['result'][$agent['id']]['last_name'] = $agent['last_name'];
            $this->out['result'][$agent['id']]['latitude'] = $agent['latitude'];
            $this->out['result'][$agent['id']]['longitude'] = $agent['longitude'];

            $distance = new GeoCalculator();
            $this->out['result'][$agent['id']]['distance'] = $distance->calculateDistance($agent['latitude'], $agent['longitude'], $lat, $lon);
        }

//        usort($this->out['result'], fn($a, $b) => $a['distance'] <=> $b['distance']);

        // Sort our results based on distance
        usort($this->out['result'], function($a, $b) {
            if ($a['distance'] > $b['distance']) {
                return 1;
            } elseif ($a['distance'] < $b['distance']) {
                return -1;
            }
            return 0;
        });

        // API output
        $this->out['error'] = '';
        $this->out['sql']['connected'] = true;
        $this->out['sql']['rebuild'] = false;

        return $this->out;
    }

    // Return agents
    public function getAgents() {
        return $this->agents;
    }
}

class GeoCalculator {
    private $earthRadius = 6371; // Radius of the Earth in kilometers

    // Calculation of distance between two different points on a map
    public function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        // Convert degrees to radians
        $lat1 = deg2rad(floatval($lat1));
        $lon1 = deg2rad(floatval($lon1));
        $lat2 = deg2rad(floatval($lat2));
        $lon2 = deg2rad(floatval($lon2));

        // Haversine formula
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $this->earthRadius * $c;

        return $distance;
    }
}

$out = [];
switch ($get) {
    case 'build':
        $build = new Build();
        $out = $build->buildData();

        break;
    case 'get_near':
        if(!empty($_GET['lat']) && !empty($_GET['lon'])) {
            $get = new Build();
            $out = $get->processDistance(floatval($_GET['lat']), floatval($_GET['lon']));
        } else {
            $out['error'] = 'No latitude and longitude provided';
        }

        break;
    default:
        $out['error'] = 'No action provided';
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($out, true);
DIE;