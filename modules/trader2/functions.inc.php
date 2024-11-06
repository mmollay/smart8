<?php

function getBrokerClientList($connection)
{
    $brokerArray = [];

    // Adjusted query to include logic for 'real_account' check
    $query = "SELECT 
                broker.user, 
                CONCAT(IF(broker.real_account = 1, 'Live - ', 'Demo - '),
                broker.user,' - ',broker.title) AS broker_name,    
                CONCAT(clients.first_name, ' ', clients.last_name) AS client_name
              FROM 
                broker 
              LEFT JOIN 
                clients ON broker.user = clients.account
              ORDER BY 
                broker.real_account, broker.title, clients.first_name, clients.last_name";

    // Execute the query
    $result = $connection->query($query);

    // Check if the query was successful
    if ($result) {
        // Fetch all entries and populate the array
        while ($row = $result->fetch_assoc()) {
            // Use 'user' as the key and combine 'broker_name' with 'client_name' (if available) as the value
            $name = $row['broker_name'];
            if (!empty($row['client_name'])) {
                $name .= ' - ' . $row['client_name'];
            }
            $brokerArray[$row['user']] = $name;
        }

        // Free the result
        $result->free();
    } else {
        // Error handling
        echo "Fehler bei der Abfrage: " . $connection->error;
    }

    return $brokerArray;
}
