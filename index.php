<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayForBlob Transaction Submission</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Submit PayForBlob Transaction</h1>
    <form id="pfb-form">
        <fieldset class="bg-white p-6 rounded-md shadow-md mb-6">
            <legend class="text-xl font-semibold mb-4">Prepare Data</legend>

            <div class="mb-4">
                <label for="data-type" class="block mb-2">Choose data type:</label>
                <select id="data-type" class="bg-white border rounded-md w-full p-2">
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="url">URL</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="input-data" class="block mb-2">Input data:</label>
                <textarea id="input-data" rows="4" cols="50" class="w-full p-2 border rounded-md"></textarea>
                <button type="button" id="encode-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-2">Hex Encode</button>
            </div>

            <div class="mb-4">
                <label for="output-data" class="block mb-2">Output data:</label>
                <textarea id="output-data" rows="4" cols="50" class="w-full p-2 border rounded-md" placeholder="This is the hex encoded data to be submitted"></textarea>
            </div>

            <div class="mb-4">
                <label for="namespace-id" class="block mb-2">Namespace ID:</label>
                <input type="text" id="namespace-id" required readonly class="w-full p-2 border rounded-md">
                <button type="button" id="generate-namespace-id-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-2">Generate Random</button>
            </div>
        </fieldset>

        <fieldset class="bg-white p-6 rounded-md shadow-md mb-6">
            <legend class="text-xl font-semibold mb-4">Node Information</legend>
            <div class="mb-4">
                <label for="node-url" class="block mb-2">Node URL:</label>
                <input type="text" id="node-url" placeholder="http://127.0.0.1:26659" required class="w-full p-2 border rounded-md">
            </div>
        </fieldset>

        <button type="button" id="submit-pfb-btn" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mb-6">Submit PFB</button>
        <div id="submit-result" class="bg-white p-4 rounded-md shadow-md mb-6 hidden" style="overflow-wrap: break-word"></div>
        <button type="button" id="verify-pfb-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">Verify Shares</button>
        <div id="verify-result" class="bg-white p-4 rounded-md shadow-md hidden" style="overflow-wrap: break-word"></div>
    </form>
</div>
<script>
    $(document).ready(function () {
        $('#submit-pfb-btn').on('click', function (e) {
            e.preventDefault();

            const namespace_id = $('#namespace-id').val();
            const data = $('#output-data').val();
            const node_url = $('#node-url').val();

            const hexEncodeRegex = /^[0-9a-fA-F]+$/;
            if (!hexEncodeRegex.test(data)) {
                alert('Data is not a valid hex encoded string.');
                return;
            }

            if (data.trim() === '') {
                alert('Data cannot be empty.');
                return;
            }

            if (!isValidURL(node_url)) {
                alert('Please enter a valid Node URL.');
                return;
            }

            submitPFBTransaction(namespace_id, data);
        });

        $('#verify-pfb-btn').on('click', function (e) {
            e.preventDefault();

            const namespace_id = $(this).data('namespaceId');
            const height = $(this).data('height');

            if (namespace_id === undefined || height === undefined) {
                alert('You must submit the PFB transaction first before you can verify.');
                return;
            }

            verifyPFBTransaction(namespace_id, height);
        });

        $('#generate-namespace-id-btn').on('click', function () {
            $('#namespace-id').val(generateRandHexEncodedNamespaceID());
        });

        $('#generate-namespace-id-btn').trigger('click');

        $('#encode-btn').on('click', function () {
            let data = $('#input-data').val();
            const dataType = $('#data-type').val();

            if (dataType === 'number') {
                data = parseInt(data);
                if (isNaN(data)) {
                    alert('Invalid number input.');
                    return;
                }
                $('#output-data').val(numberToHex(data));
            } else {
                if (dataType === 'url') {
                    try {
                        new URL(data);
                    } catch (_) {
                        alert('Invalid URL input.');
                        return;
                    }
                }
                $('#output-data').val(hexEncode(data));
            }
        });
    });

    function isValidURL(str) {
        try {
            new URL(str);
            return true;
        } catch (_) {
            return false;
        }
    }

    function hexEncode(str) {
        let result = '';
        for (let i = 0; i < str.length; i++) {
            result += str.charCodeAt(i).toString(16);
        }
        return result;
    }

    function numberToHex(num) {
        let hexString = num.toString(16);
        if (hexString.length % 2 !== 0) {
            hexString = '0' + hexString;
        }
        return hexString;
    }

    function generateRandHexEncodedNamespaceID() {
        const nID = new Uint8Array(8);
        window.crypto.getRandomValues(nID);
        const hexString = Array.prototype.map.call(nID, x => ('00' + x.toString(16)).slice(-2)).join('');
        return hexString;
    }

    function submitPFBTransaction(namespace_id, data) {
        $('#submit-pfb-btn').prop('disabled', true);
        $('#verify-pfb-btn').prop('disabled', true);
        $('#submit-result').html(`<p>Loading...</p>`);
        $('#submit-result').show();
        $('#verify-result').hide();

        const node_url = $('#node-url').val();
        $.ajax({
            type: 'POST',
            url: 'submit_pfb.php',
            dataType: 'json',
            data: {
                node_url: node_url,
                namespace_id: namespace_id,
                data: data,
                gas_limit: 80000,
                fee: 2000
            },
            success: function (response) {
                $('#submit-result').html(`<p>Transaction submitted successfully. Transaction ID: ${response.txhash}, Block Height: ${response.height}.</p>`);

                $('#verify-pfb-btn').data('namespaceId', namespace_id);
                $('#verify-pfb-btn').data('height', response.height);

                $('#submit-pfb-btn').prop('disabled', false);
                $('#verify-pfb-btn').prop('disabled', false);
            },
            error: function (error) {
                $('#submit-result').html(`<p>Error submitting transaction: ${error.responseText}</p>`);

                $('#submit-pfb-btn').prop('disabled', false);
                $('#verify-pfb-btn').prop('disabled', false);
            }
        });
    }

    function verifyPFBTransaction(namespace_id, height) {
        $('#submit-pfb-btn').prop('disabled', true);
        $('#verify-pfb-btn').prop('disabled', true);
        $('#verify-result').html(`<p>Loading...</p>`);
        $('#verify-result').show();

        const node_url = $('#node-url').val();
        $.ajax({
            type: 'POST',
            url: 'verify_pfb.php',
            dataType: 'text',
            data: {
                node_url: node_url,
                namespace_id: namespace_id,
                height: height
            },
            success: function (response) {
                $('#verify-result').html(`<p>Verified successfully: ${response}.</p>`);

                $('#submit-pfb-btn').prop('disabled', false);
                $('#verify-pfb-btn').prop('disabled', false);
            },
            error: function (error) {
                $('#verify-result').html(`<p>Error verifying transaction: ${error.responseText}</p>`);
                $('#submit-pfb-btn').prop('disabled', false);
                $('#verify-pfb-btn').prop('disabled', false);
            }
        });
    }
</script>
</body>
</html>
