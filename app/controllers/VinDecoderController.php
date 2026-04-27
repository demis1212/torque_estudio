<?php
namespace App\Controllers;

use App\Models\Vehicle;

class VinDecoderController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
        }
    }

    public function index() {
        $this->checkAuth();
        view('vin-decoder/index');
    }

    public function decode() {
        $this->checkAuth();
        
        $vin = strtoupper(trim($_POST['vin'] ?? ''));
        
        if (strlen($vin) !== 17) {
            view('vin-decoder/index', ['error' => 'El VIN debe tener 17 caracteres', 'vin' => $vin]);
            return;
        }
        
        // Decode VIN manually (basic implementation)
        $decoded = $this->decodeVin($vin);
        
        view('vin-decoder/index', [
            'vin' => $vin,
            'decoded' => $decoded
        ]);
    }

    private function decodeVin($vin) {
        // WMI - World Manufacturer Identifier (first 3 characters)
        $wmi = substr($vin, 0, 3);
        
        // VDS - Vehicle Descriptor Section (characters 4-9)
        $vds = substr($vin, 3, 6);
        
        // VIS - Vehicle Identifier Section (characters 10-17)
        $vis = substr($vin, 9, 8);
        
        // Year from character 10
        $yearCode = $vin[9];
        $year = $this->getYear($yearCode);
        
        // Plant from character 11
        $plantCode = $vin[10];
        
        // Manufacturer database (common ones)
        $manufacturers = [
            '1G1' => 'Chevrolet USA',
            '1G2' => 'Pontiac USA',
            '1G3' => 'Oldsmobile USA',
            '1G4' => 'Buick USA',
            '1G6' => 'Cadillac USA',
            '1G8' => 'Saturn USA',
            '1GC' => 'Chevrolet Truck USA',
            '1GT' => 'GMC Truck USA',
            '1GY' => 'Cadillac SUV USA',
            '1J4' => 'Jeep USA',
            '1J8' => 'Jeep USA',
            '1N4' => 'Nissan USA',
            '1N6' => 'Nissan USA',
            '1NX' => 'Toyota USA',
            '1VW' => 'Volkswagen USA',
            '19U' => 'Acura USA',
            '2G1' => 'Chevrolet Canada',
            '2G2' => 'Pontiac Canada',
            '2G4' => 'Buick Canada',
            '2G6' => 'Cadillac Canada',
            '2HG' => 'Honda Canada',
            '2HK' => 'Honda Canada',
            '2T1' => 'Toyota Canada',
            '3AL' => 'Freightliner Mexico',
            '3B7' => 'Dodge Mexico',
            '3D5' => 'Dodge Mexico',
            '3G0' => 'Saturn Mexico',
            '3G1' => 'Chevrolet Mexico',
            '3FA' => 'Ford Mexico',
            '3FE' => 'Ford Mexico',
            '3H0' => 'Honda Mexico',
            '3N1' => 'Nissan Mexico',
            '4A3' => 'Mitsubishi USA',
            '4M3' => 'Mercury USA',
            '4S4' => 'Subaru USA',
            '4S6' => 'Subaru USA',
            '4T1' => 'Toyota USA',
            '4T4' => 'Toyota USA',
            '5FN' => 'Honda USA',
            '5FNY' => 'Honda USA',
            '5J6' => 'Honda USA',
            '5LMC' => 'Lincoln USA',
            '5TD' => 'Toyota USA',
            '5TE' => 'Toyota USA',
            '5XY' => 'Kia USA',
            '55S' => 'Mercedes-Benz USA',
            '58A' => 'Lexus USA',
            '8AG' => 'Chevrolet Argentina',
            '8AK' => 'Suzuki Argentina',
            '8AP' => 'Fiat Argentina',
            '8AT' => 'Iveco Argentina',
            '9BW' => 'Volkswagen Brazil',
            '93Y' => 'Audi Brazil',
            '9BD' => 'Fiat Brazil',
            '9BS' => 'Scania Brazil',
            'JHM' => 'Honda Japan',
            'JHG' => 'Honda Japan',
            'JL5' => 'Mitsubishi Japan',
            'JM1' => 'Mazda Japan',
            'JN1' => 'Nissan Japan',
            'JN6' => 'Nissan Japan',
            'JS1' => 'Suzuki Japan',
            'JT2' => 'Toyota Japan',
            'JT3' => 'Toyota Japan',
            'JT4' => 'Toyota Japan',
            'JT6' => 'Toyota Japan',
            'JT8' => 'Toyota Japan',
            'KL1' => 'Chevrolet Korea',
            'KMH' => 'Hyundai Korea',
            'KNM' => 'Hyundai Korea',
            'KNA' => 'Kia Korea',
            'KNB' => 'Kia Korea',
            'LVG' => 'Toyota China',
            'LVH' => 'Honda China',
            'LVS' => 'Ford China',
            'LVV' => 'Chery China',
            'ML3' => 'Mitsubishi Thailand',
            'MLH' => 'Honda Thailand',
            'MM8' => 'Mazda Thailand',
            'MMH' => 'Honda Thailand',
            'MR0' => 'Toyota Thailand',
            'MRH' => 'Honda Thailand',
            'NMT' => 'Toyota Turkey',
            'NM0' => 'Ford Turkey',
            'PE1' => 'Ford Philippines',
            'PE3' => 'Mitsubishi Philippines',
            'PL1' => 'Proton Malaysia',
            'PL8' => 'Hyundai Malaysia',
            'RL1' => 'Toyota Malaysia',
            'RL4' => 'Honda Malaysia',
            'SAD' => 'Jaguar UK',
            'SAL' => 'Land Rover UK',
            'SAJ' => 'Jaguar UK',
            'SAR' => 'Rover UK',
            'SCA' => 'Rolls Royce UK',
            'SCB' => 'Bentley UK',
            'SCC' => 'Lotus UK',
            'SFD' => 'Ford UK',
            'SHH' => 'Honda UK',
            'SHS' => 'Honda UK',
            'SJA' => 'Jaguar UK',
            'TRA' => 'Iveco Italy',
            'TRU' => 'Audi Hungary',
            'TSM' => 'Suzuki Italy',
            'TMB' => 'Skoda Czech',
            'TMT' => 'Tatra Czech',
            'TM9' => 'Škoda Czech',
            'TN9' => 'Karosa Czech',
            'UU1' => 'Renault Romania',
            'UU6' => 'Dacia Romania',
            'VF1' => 'Renault France',
            'VF3' => 'Peugeot France',
            'VF6' => 'Renault France',
            'VF7' => 'Citroen France',
            'VFE' => 'Fiat France',
            'VSS' => 'SEAT Spain',
            'VV9' => 'Tesla Spain',
            'WAU' => 'Audi Germany',
            'WAP' => 'Alpina Germany',
            'WBA' => 'BMW Germany',
            'WBS' => 'BMW M Germany',
            'WBX' => 'BMW Germany',
            'WDC' => 'Daimler Germany',
            'WDD' => 'Mercedes-Benz Germany',
            'WMX' => 'Mercedes-Benz Germany',
            'WDB' => 'Mercedes-Benz Germany',
            'WP0' => 'Porsche Germany',
            'WP1' => 'Porsche Germany',
            'WVW' => 'Volkswagen Germany',
            'WV1' => 'Volkswagen Germany',
            'WV2' => 'Volkswagen Germany',
            'WV3' => 'Volkswagen Germany',
            'XL9' => 'Spyker Netherlands',
            'XMC' => 'Mitsubishi Netherlands',
            'XTA' => 'Lada Russia',
            'YK2' => 'Volvo Sweden',
            'YS3' => 'Saab Sweden',
            'YS4' => 'Scania Sweden',
            'YTN' => 'Saab Sweden',
            'YV1' => 'Volvo Sweden',
            'YV4' => 'Volvo Sweden',
            'ZAM' => 'Maserati Italy',
            'ZAP' => 'Piaggio Italy',
            'ZAR' => 'Alfa Romeo Italy',
            'ZCG' => 'Cagiva Italy',
            'ZDM' => 'Ducati Italy',
            'ZFA' => 'Fiat Italy',
            'ZFC' => 'Fiat Italy',
            'ZFF' => 'Ferrari Italy',
            'ZLA' => 'Lamborghini Italy',
            'MMM' => 'Chevrolet Dominicana',
            '6F3' => 'Chevrolet Venezuela',
            '8AK' => 'Suzuki Chile',
            '9BD' => 'Fiat Chrysler Brasil',
            'ME1' => 'Hyundai Dominicana'
        ];
        
        $manufacturer = $manufacturers[$wmi] ?? 'Desconocido (WMI: ' . $wmi . ')';
        
        // Body type from VDS (simplified)
        $bodyTypes = [
            '1' => 'Sedan 2 puertas',
            '2' => 'Sedan 4 puertas',
            '3' => 'Hatchback 2 puertas',
            '4' => 'Hatchback 4 puertas',
            '5' => 'Station Wagon',
            '6' => 'Pickup',
            '7' => 'Van',
            '8' => 'SUV',
            '9' => 'Coupe',
            'B' => 'Convertible',
        ];
        
        $bodyType = $bodyTypes[$vds[1]] ?? 'Desconocido';
        
        return [
            'vin' => $vin,
            'wmi' => $wmi,
            'manufacturer' => $manufacturer,
            'country' => $this->getCountry($wmi[0]),
            'vds' => $vds,
            'body_type' => $bodyType,
            'vis' => $vis,
            'year' => $year,
            'plant' => 'Planta: ' . $plantCode,
            'serial' => substr($vis, 2, 6),
            'check_digit' => $vin[8]
        ];
    }
    
    private function getCountry($firstChar) {
        $countries = [
            '1' => 'USA',
            '2' => 'Canada',
            '3' => 'Mexico',
            '4' => 'USA',
            '5' => 'USA',
            '6' => 'Australia',
            '7' => 'USA',
            '8' => 'Argentina',
            '9' => 'Brazil',
            'J' => 'Japan',
            'K' => 'Korea',
            'L' => 'China',
            'M' => 'Thailand',
            'N' => 'Turkey',
            'P' => 'Philippines',
            'R' => 'Taiwan',
            'S' => 'UK',
            'T' => 'Czech Republic / Hungary',
            'U' => 'Romania',
            'V' => 'France / Spain',
            'W' => 'Germany',
            'X' => 'Russia / Netherlands',
            'Y' => 'Sweden / Finland',
            'Z' => 'Italy'
        ];
        return $countries[$firstChar] ?? 'Desconocido';
    }
    
    private function getYear($code) {
        $years = [
            'A' => '2010', 'B' => '2011', 'C' => '2012', 'D' => '2013',
            'E' => '2014', 'F' => '2015', 'G' => '2016', 'H' => '2017',
            'J' => '2018', 'K' => '2019', 'L' => '2020', 'M' => '2021',
            'N' => '2022', 'P' => '2023', 'R' => '2024', 'S' => '2025',
            'T' => '2026', 'V' => '2027', 'W' => '2028', 'X' => '2029',
            'Y' => '2030', '1' => '2001', '2' => '2002', '3' => '2003',
            '4' => '2004', '5' => '2005', '6' => '2006', '7' => '2007',
            '8' => '2008', '9' => '2009'
        ];
        return $years[$code] ?? 'Desconocido';
    }

    public function lookupVehicle() {
        $this->checkAuth();
        
        $vin = $_POST['vin'] ?? '';
        $vehicleModel = new Vehicle();
        
        // Search for vehicle by VIN
        $vehicles = $vehicleModel->all();
        $foundVehicle = null;
        
        foreach ($vehicles as $vehicle) {
            if (isset($vehicle['vin']) && strtoupper($vehicle['vin']) === strtoupper($vin)) {
                $foundVehicle = $vehicle;
                break;
            }
        }
        
        if ($foundVehicle) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/vehicles/edit/' . $foundVehicle['id']);
        } else {
            // Decode and show suggestion to create vehicle
            $decoded = $this->decodeVin(strtoupper($vin));
            view('vin-decoder/index', [
                'vin' => $vin,
                'decoded' => $decoded,
                'not_found' => true
            ]);
        }
    }
}
