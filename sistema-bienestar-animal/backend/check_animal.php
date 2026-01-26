<?php
require_once '/var/www/html/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$animal = App\Models\Animal\Animal::where('nombre', 'Criollo')->first();

if ($animal) {
    echo "Animal: " . $animal->nombre . "\n";
    echo "Esterilizacion: " . ($animal->esterilizacion === true ? 'TRUE' : ($animal->esterilizacion === false ? 'FALSE' : 'NULL')) . "\n";
    echo "Raw value: " . var_export($animal->esterilizacion, true) . "\n";
} else {
    echo "Animal not found\n";
}
?>
