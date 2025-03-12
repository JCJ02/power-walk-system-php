<?php
include('databaseConnection.php');

$battery = isset($_POST['battery']) ? floatval($_POST['battery']) : 0;
$battVol = isset($_POST['battVol']) ? floatval($_POST['battVol']) : 0;
$uid = isset($_POST['uid']) ? mysqli_real_escape_string($connection, $_POST['uid']) : '';
$electGen = isset($_POST['electGen']) ? floatval($_POST['electGen']) : 0;

// FETCH LATEST ELECTRCITY DATA
$query21a1ss5 = mysqli_query($connection, "SELECT totalHours, electricityConsumption, electricityGenerated FROM electricity WHERE id=1") or die(mysqli_error($connection));
$count_cust2ss5 = mysqli_fetch_assoc($query21a1ss5);

if ($count_cust2ss5) {
    $totalHours = isset($count_cust2ss5['totalHours']) ? floatval($count_cust2ss5['totalHours']) : 0;
    $currentEnergyConsumed = isset($count_cust2ss5['electricityConsumption']) ? floatval($count_cust2ss5['electricityConsumption']) : 0;
    $currentEnergyGenerated = isset($count_cust2ss5['electricityGenerated']) ? floatval($count_cust2ss5['electricityGenerated']) : 0;

    // UPDATE BATTERY DATA (UNCHANGED)
    mysqli_query($connection, "UPDATE battery SET batteryPercentage='$battery', batteryVoltage='$battVol' WHERE id=1") or die(mysqli_error($connection));

    // UPDATE ELECTRICITY DATA
    $newEnergyGenerated = $currentEnergyGenerated + $electGen;
    mysqli_query($connection, "UPDATE electricity SET electricityGenerated='$newEnergyGenerated' WHERE id=1") or die(mysqli_error($connection));
}

// CHECK IF RFID EXISTS
$query22 = mysqli_query($connection, "SELECT * FROM rfid WHERE uid='$uid'") or die(mysqli_error($connection));
$row22 = mysqli_num_rows($query22);

$date = date("Y-m-d");
// $query222 = mysqli_query($connection, "SELECT * FROM history WHERE uid2='$uid' AND date_added='$date'") or die(mysqli_error($connection));
$query222 = mysqli_query($connection, "SELECT * FROM history WHERE rfid_uid='$uid' AND createdAt='$date'") or die(mysqli_error($connection));
$row222 = mysqli_num_rows($query222);

if ($row22 >= 1) {
    if ($row222 == 0) {
        echo "Yes";
        // mysqli_query($con, "INSERT INTO history(uid2, date_added) VALUES('$uid', '$date')") or die(mysqli_error($con));
        mysqli_query($connection, "INSERT INTO history(rfid_uid, createdAt, updatedAt) VALUES('$uid', '$date', NOW())") or die(mysqli_error($connection));

        // CALCULATE ENERGY CONSUMED FOR THIS RFID SCAN (12V * CURRENT * 0.25H)
        $energyConsumed = 12 * 0.735 * 0.25; // Wh
        $newEnergyConsumed = $currentEnergyConsumed + $energyConsumed;

        // UPDATE ELECTRICITY DATA
        mysqli_query($connection, "UPDATE electricity SET electricityConsumption='$newEnergyConsumed', totalHours=totalHours + 0.25 WHERE id=1") or die(mysqli_error($connection));

        // INSERT ELECTRICITY CONSUMPTION PER DAY
        mysqli_query($connection, "INSERT INTO electricity_meter(dailyElectricityConsumption, dailyElectricityGenerated, updatedAt) VALUES('$energyConsumed', '$electGen', NOW())") or die(mysqli_error($connection));
    } else {
        echo "ALREADY USED!";
    }
} else {
    echo "INVALID RFID!";
}
