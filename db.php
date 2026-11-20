<?php
$host = 'localhost';
$dbname = 'office_route';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

function report_month_previous(): string
{
    return date('Y-m', strtotime('first day of last month'));
}

function report_month_value(string $month): string
{
    $month = trim($month);
    if (preg_match('/^(\d{4})-(\d{2})$/', $month, $matches)
        && (int) $matches[2] >= 1
        && (int) $matches[2] <= 12) {
        return $month;
    }

    if (!preg_match('/^(.+?)\s+(\d{4})$/u', $month, $matches)) {
        return '';
    }

    $nom_mois = strtoupper($matches[1]);
    $nom_mois = strtr($nom_mois, ['é' => 'É', 'û' => 'Û', 'ô' => 'Ô', 'à' => 'À', 'è' => 'È']);
    $mois = [
        'JANVIER' => '01',
        'FÉVRIER' => '02',
        'MARS' => '03',
        'AVRIL' => '04',
        'MAI' => '05',
        'JUIN' => '06',
        'JUILLET' => '07',
        'AOÛT' => '08',
        'SEPTEMBRE' => '09',
        'OCTOBRE' => '10',
        'NOVEMBRE' => '11',
        'DÉCEMBRE' => '12'
    ];

    return isset($mois[$nom_mois]) ? $matches[2] . '-' . $mois[$nom_mois] : '';
}

function report_month_label(string $month): string
{
    $month = trim($month);
    if ($month === '') {
        $month = report_month_previous();
    }

    $value = report_month_value($month);
    if ($value === '') {
        return $month;
    }

    $mois = [
        'JANVIER', 'FÉVRIER', 'MARS', 'AVRIL', 'MAI', 'JUIN',
        'JUILLET', 'AOÛT', 'SEPTEMBRE', 'OCTOBRE', 'NOVEMBRE', 'DÉCEMBRE'
    ];
    $numero_mois = (int) substr($value, 5, 2);

    return $mois[$numero_mois - 1] . ' ' . substr($value, 0, 4);
}
?>
