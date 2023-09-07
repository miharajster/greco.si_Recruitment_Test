<?php
namespace App;
// CONF
class Config {
    const xml           = './import/unesco.xml';
    const first_name    = './import/first-names.txt';
    const last_name     = './import/last-names.txt';
    const db            = '../../identifier.sqlite';
}

// let's see if there's anything that user want from us
$get = null;
if(isset($_GET['action'])) {
    $get = $_GET['action'];
}

class SQL {
    private $pdo;
    public function connect() {
        if ($this->pdo == null) {
            $this->pdo = new \PDO("sqlite:" . Config::db);
        }
        return $this->pdo;
    }

    public function rewrite($data) {
        $pdo = (new SQL())->connect();
        if ($pdo != null) {
            // Connected to the SQLite database successfully!

            // Clear database
            $sql = 'DELETE FROM agents WHERE 1=1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            foreach($data as $agent){
                $sql = 'INSERT INTO agents(id,first_name,last_name,latitude,longitude) '.
                       'VALUES(:id,:first_name,:last_name,:latitude,:longitude)';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id'           => $agent['id'],
                    ':first_name'   => $agent['name'],
                    ':last_name'    => $agent['surname'],
                    ':latitude'     => $agent['location']['lat'],
                    ':longitude'    => $agent['location']['long'],
                ]);
            }
        } else {
            die('Whoops, could not connect to the SQLite database!');
        }
        $this->pdo = NULL;
    }

    public function getAll() {
        $pdo = (new SQL())->connect();
        if ($pdo != null) {
            // Connected to the SQLite database successfully!
            $sql = 'SELECT * FROM agents ORDER BY id ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll();

            die();
        } else {
            die('Whoops, could not connect to the SQLite database!');
        }
        $this->pdo = NULL;
    }
}

class Build {
    private $agents = [];
    private $out = [];

    public function buildData() {
        $xml_file = file_get_contents(Config::xml);
        $xml = simplexml_load_string($xml_file, "SimpleXMLElement", LIBXML_NOCDATA);

        $fn_file = file_get_contents(Config::first_name);
        $fn = preg_split("/\\r\\n|\\r|\\n/", $fn_file);

        $ln_file = file_get_contents(Config::last_name);
        $ln = preg_split("/\\r\\n|\\r|\\n/", $ln_file);

        for ($i = 0; $i < sizeof($xml); $i++) {
            $this->agents[$i]['id'] = $i;
            $this->agents[$i]['name'] = $fn[rand(0, sizeof($fn) - 1)];
            $this->agents[$i]['surname'] = $ln[rand(0, sizeof($ln) - 1)];
            $this->agents[$i]['location'] = [
                'lat' => $xml->row[$i]->latitude->__toString(),
                'long' => $xml->row[$i]->longitude->__toString()
            ];
        }

        $sql = new SQL();
        $sql->rewrite($this->agents);

        $this->out['error'] = '';
        $this->out['sql']['connected'] = true;
        $this->out['sql']['rebuild'] = true;
        $this->out['result'] = [];

        return $this->out;
    }

    public function processDistance($lat, $lon) {
        $this->out['error'] = '';
        $this->out['sql']['connected'] = true;
        $this->out['sql']['rebuild'] = false;

        $sql = new SQL();
        $sql_agents = $sql->getAll();

        if(sizeof($sql_agents) == 0) {
            $build = new Build();
            $build->buildData();
        }

        foreach($sql_agents as $agent) {
            $this->out['result'][$agent['id']]['id'] = $agent['id'];
            $this->out['result'][$agent['id']]['first_name'] = $agent['first_name'];
            $this->out['result'][$agent['id']]['last_name'] = $agent['last_name'];
            $this->out['result'][$agent['id']]['latitude'] = $agent['latitude'];
            $this->out['result'][$agent['id']]['longitude'] = $agent['longitude'];

            $distance = new GeoCalculator();
            $this->out['result'][$agent['id']]['distance'] = $distance->calculateDistance($agent['latitude'], $agent['longitude'], $lat, $lon);
        }

        usort($this->out['result'], fn($a, $b) => $a['distance'] <=> $b['distance']);

        return $this->out;
    }

    public function getAgents() {
        return $this->agents;
    }
}

class GeoCalculator {
    private $earthRadius = 6371; // Radius of the Earth in kilometers

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
            $out = $get->processDistance($_GET['lat'], $_GET['lon']);
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