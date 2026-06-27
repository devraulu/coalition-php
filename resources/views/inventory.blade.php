<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stock Inventory</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Stock Inventory</h1>

        <div id="form-error" class="alert alert-danger d-none"></div>

        <form id="item-form" class="row g-3 mb-5">
            <div class="col-md-4">
                <label for="product_name" class="form-label">Product name</label>
                <input type="text" class="form-control" id="product_name" name="product_name" required>
            </div>
            <div class="col-md-3">
                <label for="quantity" class="form-label">Quantity in stock</label>
                <input type="number" class="form-control" id="quantity" name="quantity" min="0" step="1" required>
            </div>
            <div class="col-md-3">
                <label for="price" class="form-label">Price per item</label>
                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Add</button>
            </div>
        </form>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Product name</th>
                    <th>Quantity in stock</th>
                    <th>Price per item</th>
                    <th>Created at</th>
                    <th>Total value</th>
                </tr>
            </thead>
            <tbody id="item-rows">
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item['product_name'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ number_format($item['price'], 2) }}</td>
                        <td>{{ $item['datetime'] }}</td>
                        <td>{{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Total</th>
                    <th id="grand-total">{{ number_format($total, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <script>
        const form = document.getElementById('item-form');
        const rows = document.getElementById('item-rows');
        const grandTotal = document.getElementById('grand-total');
        const errorBox = document.getElementById('form-error');
        const token = document.querySelector('meta[name="csrf-token"]').content;

        function renderRows(items, total) {
            rows.innerHTML = '';

            items.forEach((item) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.product_name}</td>
                    <td>${item.quantity}</td>
                    <td>${Number(item.price).toFixed(2)}</td>
                    <td>${item.datetime}</td>
                    <td>${Number(item.total).toFixed(2)}</td>
                `;
                rows.appendChild(tr);
            });

            grandTotal.textContent = Number(total).toFixed(2);
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            errorBox.classList.add('d-none');

            fetch('/items', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_name: document.getElementById('product_name').value,
                    quantity: document.getElementById('quantity').value,
                    price: document.getElementById('price').value,
                }),
            })
                .then((response) => response.json().then((data) => ({ status: response.status, data })))
                .then(({ status, data }) => {
                    if (status !== 200) {
                        const messages = data.errors ? Object.values(data.errors).flat().join(' ') : 'Something went wrong.';
                        errorBox.textContent = messages;
                        errorBox.classList.remove('d-none');
                        return;
                    }

                    renderRows(data.items, data.total);
                    form.reset();
                })
                .catch(() => {
                    errorBox.textContent = 'Something went wrong.';
                    errorBox.classList.remove('d-none');
                });
        });
    </script>
</body>
</html>
