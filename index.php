<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details Submission</title>
</head>
<body>
    <h1>Submit Job Details</h1>
    <button id="submit">Submit Job Details</button>

    <script>
        document.getElementById('submit').addEventListener('click', function() {
            const data = {
                job_id: 'some_job_id',
                po_number: '8800305571',
                challan_date: '2024-05-29',
                manufacturer_name: 'SANMAR MATRIX METALS LIMITED',
                total_quantity: 'some_total_quantity',
                total_weight: 'some_total_weight',
                data: [
                    {
                        "Sr No": "1",
                        "Material Code": "2000017173",
                        "Quantity": "57.00",
                        "Unit/Wt": "1.545",
                        "Est value": "27987.00",
                        "HSN Code": "7325 9999",
                        "Scope": "Gate/Proof Machining",
                        "Desc. of Goods": "Brake shoe,casting 5 Ton ASTM A148 80-50",
                        "Total Wt": "88.065 KG",
                        "Due date": "2024-06-01"
                    },
                    {
                        "Sr No": "2",
                        "Material Code": "2000017173",
                        "Quantity": "43.00",
                        "Unit/Wt": "1.545",
                        "Est value": "21113.00",
                        "HSN Code": "7325 9999",
                        "Scope": "Gate/Proof Machining",
                        "Desc. of Goods": "Brake shoe,casting 5 Ton ASTM A148 80-50",
                        "Total Wt": "66.435 KG",
                        "Due date": "2024-06-01"
                    }
                ]
            };

            fetch('insert.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
