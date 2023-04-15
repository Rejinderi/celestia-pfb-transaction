<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayForBlob Transaction Submission</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        input, button {
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<h1>Submit a PayForBlob Transaction</h1>
<form id="pfb-form">
    <fieldset>
        <legend>Prepare Data</legend>

        <label for="data-type">Choose data type:</label>
        <select id="data-type">
            <option value="text">Text</option>
            <option value="number">Number</option>
            <option value="url">URL</option>
        </select>
        <br>
        <label for="input-data">Input data:</label>
        <br>
        <textarea id="input-data" rows="4" cols="50"></textarea>
        <button type="button" id="encode-btn">Hex Encode</button>
        <label for="output-data">Output data:</label>
        <br>
        <textarea id="output-data" rows="4" cols="50"
                  placeholder="This is the hex encoded data to be submitted"></textarea>
        <br>
        <label for="namespace-id">Namespace ID:</label>
        <br>
        <input type="text" id="namespace-id" required readonly>
        <button type="button" id="generate-namespace-id-btn">Generate Random</button>
    </fieldset>
    <fieldset>
        <legend>Node Information</legend>
        <label for="node-url">Node URL:</label>
        <input type="text" id="node-url" placeholder="http://127.0.0.1:26659" required>
    </fieldset>
    <br>
    <button type="button" id="submit-pfb-btn">Submit PFB</button>
    <div id="submit-result"></div>
    <button type="button" id="verify-pfb-btn">Verify Shares</button>
    <div id="verify-result"></div>
</form>
<script>
    $(document).ready(function () {
        $('#submit-pfb-btn').on('click', function (e) {
            e.preventDefault();

            const namespace_id = $('#namespace-id').val();
            const data = $('#output-data').val();

            const hexEncodeRegex = /^[0-9a-fA-F]+$/;
            if (!hexEncodeRegex.test(data)) {
                alert('Data is not a valid hex encoded string.');
                return;
            }

            if (data.trim() === '') {
                alert('Data cannot be empty.');
                return;
            }

            submitPFBTransaction(namespace_id, data);
        });

        $('#verify-pfb-btn').on('click', function (e) {
            e.preventDefault();

            const namespace_id = $(this).data('namespaceId');
            const height = $(this).data('height');

            if (namespace_id === undefined || height === undefined) {
                alert('You must submit the PFB first before you can verify.');
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

    function hexEncode(str) {
        let result = '';
        for (let i = 0; i < str.length; i++) {
            result += str.charCodeAt(i).toString(16);
        }
        return result;
    }

    function numberToHex(num) {
        return num.toString(16);
    }

    function generateRandHexEncodedNamespaceID() {
        const nID = new Uint8Array(8);
        window.crypto.getRandomValues(nID);
        const hexEncoded = Array.prototype.map.call(nID, x => ('00' + x.toString(16)).slice(-2)).join('');
        return hexEncoded;
    }

    function submitPFBTransaction(namespace_id, data) {
        $('#submit-pfb-btn').prop('disabled', true);
        $('#verify-pfb-btn').prop('disabled', true);
        $('#submit-result').html(`<p>Loading...</p>`);

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
