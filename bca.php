<?php
header('Content-Type: text/html; charset=UTF-8');

$historyFile = 'history.json';

// Function to calculate percentage change
function calculatePercentageChange($newRate, $oldRate) {
    if ($oldRate == 0) return 0;
    return (($newRate - $oldRate) / $oldRate) * 100;
}

// Function to get current date-time in GMT+7
function getCurrentTimeInGMTPlus7() {
    $timezone = new DateTimeZone('Asia/Jakarta'); // GMT+7 timezone
    $dateTime = new DateTime('now', $timezone);
    return $dateTime->format('Y-m-d H:i:s');
}

// Handle AJAX requests for fetching data
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    // Fetch current rates from API
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://www.bca.co.id/api/sitecore/currencies/RefreshKurs",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => "dsid=%7B83966849-FC44-4A3E-97D0-8F96D7388B22%7D",
        CURLOPT_HTTPHEADER => [
            "accept: */*",
            "accept-language: en-US,en;q=0.9",
            "content-type: application/x-www-form-urlencoded; charset=UTF-8",
            "x-requested-with: XMLHttpRequest",
            "Referer: https://www.bca.co.id/",
            "Referrer-Policy: strict-origin"
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);

    // Save current data to history file
    if ($data) {
        file_put_contents($historyFile, json_encode($data));
        $lastFetchDate = getCurrentTimeInGMTPlus7();
    } else {
        $lastFetchDate = null;
    }

    // Load historical data
    $historicalData = json_decode(file_get_contents($historyFile), true);

    // Prepare data for response
    $result = [
        'current' => $data['KursRates'] ?? [],
        'history' => $historicalData['KursRates'] ?? [],
        'lastFetchDate' => $lastFetchDate,
    ];
    echo json_encode($result);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currency Rates</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .loading-button {
            position: relative;
        }
        .loading-button:disabled::after {
            content: 'Loading...';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #fff;
        }
        .hidden {
            display: none;
        }
      .loading-button {
    position: relative;
    color: transparent;
}

.loading-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, rgba(255, 255, 255, 0) 25%, rgba(255, 255, 255, 0.5) 50%, rgba(255, 255, 255, 0) 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    z-index: 1;
}

.loading-button::after {
    content: 'Loading...';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #fff;
    z-index: 2;
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}
/* Overlay for the entire page */
#page-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: none; /* Hidden by default */
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

/* Spinner animation */
.spinner {
    border: 8px solid rgba(0, 0, 0, 0.1);
    border-left-color: #3498db;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

    </style>
    <script>
        // Function to calculate percentage change
        function calculatePercentageChange(newRate, oldRate) {
            if (oldRate === 0) return 0;
            return ((newRate - oldRate) / oldRate) * 100;
        }

     let manualFetch = false; // Flag to track manual button clicks

async function fetchData(isManual = false) {
    const fetchStatus = document.getElementById('fetch-status');
    const forceUpdateBtn = document.getElementById('force-update-btn');
    
    if (isManual) {
        // Only disable the button and show loading state if triggered manually
        fetchStatus.classList.add('hidden');
        forceUpdateBtn.disabled = true;
        forceUpdateBtn.classList.add('loading-button');
    }

    try {
        const response = await fetch('?action=fetch');
        const data = await response.json();

        if (data.error) {
            document.getElementById('error').innerText = data.error;
            fetchStatus.innerText = 'Failed to fetch data.';
            fetchStatus.classList.remove('hidden');
            return;
        }

        const currentRates = data.current;
        const historicalRates = data.history;
        const lastFetchDate = data.lastFetchDate;

        const tableBody = document.getElementById('data-body');
        const changeBody = document.getElementById('change-body');
        tableBody.innerHTML = '';
        changeBody.innerHTML = '';

        currentRates.forEach((currency, index) => {
            const historicalCurrency = historicalRates[index] || {};

            const createRow = (values) => `<tr class="border-b border-gray-200 hover:bg-gray-100">
                ${values.map(value => `<td class="py-3 px-6 text-left">${value}</td>`).join('')}
            </tr>`;

            tableBody.innerHTML += createRow([
                currency['CurrencyCode'],
                currency['CurrencyName'],
                currency['Rates'][0]['SellRate'].toFixed(2),
                currency['Rates'][0]['BuyRate'].toFixed(2),
                currency['Rates'][1]['SellRate'].toFixed(2),
                currency['Rates'][1]['BuyRate'].toFixed(2),
                currency['Rates'][2]['SellRate'].toFixed(2),
                currency['Rates'][2]['BuyRate'].toFixed(2)
            ]);

            changeBody.innerHTML += createRow([
                currency['CurrencyCode'],
                calculatePercentageChange(currency['Rates'][0]['SellRate'], historicalCurrency['Rates'][0]['SellRate']).toFixed(2) + '%',
                calculatePercentageChange(currency['Rates'][0]['BuyRate'], historicalCurrency['Rates'][0]['BuyRate']).toFixed(2) + '%',
                calculatePercentageChange(currency['Rates'][1]['SellRate'], historicalCurrency['Rates'][1]['SellRate']).toFixed(2) + '%',
                calculatePercentageChange(currency['Rates'][1]['BuyRate'], historicalCurrency['Rates'][1]['BuyRate']).toFixed(2) + '%',
                calculatePercentageChange(currency['Rates'][2]['SellRate'], historicalCurrency['Rates'][2]['SellRate']).toFixed(2) + '%',
                calculatePercentageChange(currency['Rates'][2]['BuyRate'], historicalCurrency['Rates'][2]['BuyRate']).toFixed(2) + '%'
            ]);
        });

        if (lastFetchDate) {
            document.getElementById('last-fetch').innerText = `Last updated: ${lastFetchDate}`;
        }

        fetchStatus.innerText = 'Data fetched successfully.';
        fetchStatus.classList.remove('hidden');
        
        // Hide the success message after 2 seconds
        setTimeout(() => {
            fetchStatus.classList.add('hidden');
        }, 2000);

    } catch (error) {
        console.error('Error fetching data:', error);
        fetchStatus.innerText = 'Error fetching data.';
        fetchStatus.classList.remove('hidden');
    } finally {
        if (isManual) {
            // Re-enable button and remove loading state if triggered manually
            forceUpdateBtn.disabled = false;
            forceUpdateBtn.classList.remove('loading-button');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const forceUpdateBtn = document.getElementById('force-update-btn');
    const pageLoadingOverlay = document.getElementById('page-loading-overlay');
    const lastUpdated = document.getElementById('last-updated');

    async function fetchData(isManual = false) {
        try {
            // Show the page loading overlay
            pageLoadingOverlay.style.display = 'flex';

            if (isManual) {
                // Change the button to a loading state and disable it
                forceUpdateBtn.disabled = true;
                forceUpdateBtn.classList.add('loading-button');
            }

            const response = await fetch('?action=fetch');
            const data = await response.json();

            // Check if the data is correctly fetched
            if (data && data.current) {
                renderData(data.current);
                updateLastFetchedTime();
            } else {
                console.error('No data received or data structure is incorrect.');
            }

        } catch (error) {
            console.error('Error fetching data:', error);
        } finally {
            // Hide the page loading overlay
            pageLoadingOverlay.style.display = 'none';

            if (isManual) {
                // Re-enable the button and reset its content
                forceUpdateBtn.disabled = false;
                forceUpdateBtn.classList.remove('loading-button');
            }
        }
    }

    function renderData(currentRates) {
        const tableBody = document.getElementById('data-body');
        tableBody.innerHTML = ''; // Clear the table before rendering new data

        currentRates.forEach((currency) => {
            const createRow = (values) => `<tr class="border-b border-gray-200 hover:bg-gray-100">
                ${values.map(value => `<td class="py-3 px-6 text-left">${value}</td>`).join('')}
            </tr>`;

            tableBody.innerHTML += createRow([
                currency['CurrencyCode'],
                currency['CurrencyName'],
                currency['Rates'][0]['SellRate'].toFixed(2),
                currency['Rates'][0]['BuyRate'].toFixed(2),
                currency['Rates'][1]['SellRate'].toFixed(2),
                currency['Rates'][1]['BuyRate'].toFixed(2),
                currency['Rates'][2]['SellRate'].toFixed(2),
                currency['Rates'][2]['BuyRate'].toFixed(2)
            ]);
        });
    }

    function updateLastFetchedTime() {
    const now = new Date();

    // Format the date and time in GMT+7
    const formatter = new Intl.DateTimeFormat('en-US', {
        timeZone: 'Asia/Jakarta', // Correct time zone for GMT+7
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false // Use 24-hour format
    });

    // Format the current date and time
    const formattedTime = formatter.format(now);

    // Update the last-updated element
    document.getElementById('last-updated').textContent = `Last updated: ${formattedTime} (GMT+7)`;
}


    // Initial fetch on page load
    fetchData();

    // Set up auto-refresh every 60 seconds
    setInterval(() => fetchData(), 60000);

    // Manual fetch when clicking the "Force Update" button
    forceUpdateBtn.addEventListener('click', () => {
        fetchData(true);
    });
});



document.getElementById('force-update-btn').addEventListener('click', function() {
    manualFetch = true;
    fetchData(true); // Pass true to disable the button and show loading state
});


    </script>
</head>
<body class="bg-gray-100 p-6">
  <div id="page-loading-overlay" class="flex">
        <div class="spinner"></div>
    </div>
    <div class="container mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-center">Bank Central Asia Currency Exchange Rates</h1>

        <button id="force-update-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">
    Force Update
</button>



        <div id="fetch-status" class="text-center mb-6 hidden"></div>
      
        <div id="last-updated" class="text-sm text-gray-500 mt-4"></div>

        <div id="error" class="text-center text-red-500 mb-6"></div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg shadow-md">
                <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <tr>
                        <th class="py-3 px-6 text-left">Currency Code</th>
                        <th class="py-3 px-6 text-left">Currency Name</th>
                        <th class="py-3 px-6 text-left">Sell Rate (e-Rate)</th>
                        <th class="py-3 px-6 text-left">Buy Rate (e-Rate)</th>
                        <th class="py-3 px-6 text-left">Sell Rate (TT Counter)</th>
                        <th class="py-3 px-6 text-left">Buy Rate (TT Counter)</th>
                        <th class="py-3 px-6 text-left">Sell Rate (Bank Notes)</th>
                        <th class="py-3 px-6 text-left">Buy Rate (Bank Notes)</th>
                    </tr>
                </thead>
                <tbody id="data-body" class="text-gray-600 text-sm font-light"></tbody>
            </table>
        </div>

        <div class="mt-6">
            <h2 class="text-xl font-bold mb-4 text-center">Percentage Change in the Last Hour</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg shadow-md">
                    <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <tr>
                            <th class="py-3 px-6 text-left">Currency Code</th>
                            <th class="py-3 px-6 text-left">Sell Rate (e-Rate)</th>
                            <th class="py-3 px-6 text-left">Buy Rate (e-Rate)</th>
                            <th class="py-3 px-6 text-left">Sell Rate (TT Counter)</th>
                            <th class="py-3 px-6 text-left">Buy Rate (TT Counter)</th>
                            <th class="py-3 px-6 text-left">Sell Rate (Bank Notes)</th>
                            <th class="py-3 px-6 text-left">Buy Rate (Bank Notes)</th>
                        </tr>
                    </thead>
                    <tbody id="change-body" class="text-gray-600 text-sm font-light"></tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
