<?php
/**
 * This module includes all the code necessary for generating tables that can be printed
 * onto receipt paper.
 */

include_once PUBLIC_FILES . '/modules/inventoryFunctions.php';


/**
 * Creates an inventory table that can be printed onto receipt paper to easily take to
 * the inventory room & collect the needed parts.
 * 
 * @param boolean $quantityEditable Whether to display the quantity as an editable field
 * 
 * @return string Raw HTML to include on the page
 */
function createCartReceiptTable($cart, $quantityEditable) {
    $table = '<table class="table" id="InventoryTable" style="width: 100%; max-width: 100%; table-layout:fixed;">
        <thead>
            <tr>
                <th style="width:30%">Item</th>
                <th style="width:15%">Loc</th>
                <th style="width:15%" class="d-none d-md-table-cell">Price</th>
                <th style="width:15%">QTY</th>
                <th style="width:15%">Stock</th>
                <th style="width:10%" class="d-none d-md-table-cell">Reduce Stock</th>
                <th class="d-none">Item</th>
                <th class="d-none">Item Info</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($cart->getContents() as ['quantity' => $quantity, 'part' => $p]) {
        if ($quantity == 0 || $p->getArchive()) {
            continue;
        }
        
        $stocknumber = $p->getStocknumber();
        $studentPriceStr = numberToDollarString($p->getMarketPrice() ?: getStudentPrice($p->getLastPrice()));
        $inventoryQuantity = $p->getQuantity();

        $table .= "<tr>
            <td>
                <a href='./pages/publicInventoryPart.php?stocknumber=$stocknumber' style='text-decoration:none;'>
                    {$p->getType()}: <BR>{$p->getName()}
                </a>
            </td>
            <td>{$p->getLocation()}</td>
            <td class='d-none d-md-table-cell'>$studentPriceStr</td>
            <td>
                <input 
                    type='number' min='0' value='{$quantity}'
                    class='form-control cart-quantity-input' style='width: 80px;'
                    onchange=\"(function() {
                            setPartQuantityInCart(
                                '{$cart->getIdKey()}',
                                '{$stocknumber}',
                                this.value,
                                (Number('{$inventoryQuantity}') < Number(this.value) ? Number('{$inventoryQuantity}') : false)
                            );
                            
                            const row = this.closest('tr');
                            const hiddenQty = row.querySelector('.hiddenCartQty'); 
                            hiddenQty.innerText = this.value;
                        }
                    ).call(this)\"
                />
            </td>
            <td class='inventory-stock'>$inventoryQuantity</td>
            <td class='d-none d-md-table-cell'>
                <button type='button' class='btn btn-outline-primary' onclick=\"removeInventoryStock(this, '{$stocknumber}')\">
                    Decrement
                </button>
            </td>
            <td class='d-none' style='font-size: 18px; white-space: normal !important;'>
                {$p->getType()}: <BR><b>{$p->getName()}</b>
            </td>
            <td class='d-none' style='font-size: 18px; white-space: normal !important;'>
                Cart Quantity: <b class='hiddenCartQty'>{$quantity}</b><br>Location: <span style='font-weight: bold;'>{$p->getLocation()}</span><br>In-Stock: <span class='hidden-inventory-stock'>$inventoryQuantity</span>
            </td>
        </tr>";
	}
    
    $table .= '</tbody>
        </table>
        <script>
            function removeInventoryStock(button, stocknumber){
                const row = button.closest("tr");
                const stockData = row.querySelector(".inventory-stock"); 
                const quantity = Number(stockData.innerText);
                
                const cartQtyInput = row.querySelector(".cart-quantity-input"); 
                let removeQty = Math.min(Number(cartQtyInput.value), quantity);

                updateInventoryQuantityByAmount(stocknumber, -removeQty);

                // Redundant if window reloads, but some refreshes dont refresh the page due to caching
                const newInventoryQty = Number(quantity) - Number(removeQty);	
                stockData.innerText = newInventoryQty;

                const hiddenStockData = row.querySelector(".hidden-inventory-stock");
                hiddenStockData.innerText = newInventoryQty;
            }

            function updateInventoryQuantityByAmount(id, amount){
                let content = {
                    action: "updateInventoryQuantityByAmount",
                    stockNumber: id,
                    amount: amount
                }
                
                api.post("/inventory.php", content).then(res => {
                    snackbar(res.message, "info");
                }).catch(err => {
                    snackbar(err.message, "error");
                });
            }
        </script>
    ';

    return $table;
}


/**
 * Creates an inventory table that can be printed onto receipt paper to easily take to
 * the inventory room & collect the needed parts.
 * 
 * @param boolean $quantityEditable Whether to display the quantity as an editable field
 * 
 * @return string Raw HTML to include on the page
 */
function createTransactionReceiptTable($inventoryDao, $items) {
    $table = '<table class="table" id="InventoryTable" style="width: 100%; max-width: 100%; table-layout:fixed;">
        <thead>
            <tr>
                <th style="width:30%">Item</th>
                <th style="width:15%">Loc</th>
                <th style="width:15%; display: none;" class="d-md-table-cell">Price</th>
                <th style="width:10%">QTY</th>
                <th style="width:10%; display: none;" class="d-md-table-cell">Refund</th>
                <th style="width:15%">Stock</th>
                <th style="width:10%; display: none;" class="d-md-table-cell">Reduce Stock</th>
                <th class="d-none">Item</th>
                <th class="d-none">Item Info</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($items as $i) {
        $stocknumber = $i->getStocknumber();
        $finalPriceStr = numberToDollarString($i->getPrice());

        $part = $inventoryDao->getPartByStocknumber($stocknumber) ?: null;

        $quantity = $i->getQuantity() - $i->getQuantityRefunded();
        $table .= "<tr class='item'>
            <td>
                <a
                    href='./pages/publicInventoryPart.php?stocknumber=$stocknumber'
                    class='item-name'
                    style='text-decoration:none;'
                >
                    {$i->getType()}: <BR>{$i->getName()}
                </a>
            </td>
            <td>{$part?->getLocation()}</td>
            <td class='d-md-table-cell' style='display: none;'>$finalPriceStr</td>
            <td class='current-quantity'>$quantity</td>
            <td class='d-md-table-cell' style='display: none;'>
                <input
                    type='number'
                    class='form-control item-refund-input'
                    value='{$i->getQuantityRefunded()}'
                    data-item-id='{$i->getItemID()}'
                    min='{$i->getQuantityRefunded()}' max='{$i->getQuantity()}'
                >
            </td>
            <td class='inventory-stock'>{$part->getQuantity()}</td>
            <td class='d-md-table-cell' style='display: none;'>
                <button
                    ".($quantity ? '' : 'disabled')."
                    class='btn btn-outline-primary'
                    type='button' onclick=\"removeInventoryStock(this, '{$stocknumber}')\"
                >
                    Decrement
                </button>
            </td>
            <td class='d-none' style='font-size: 18px; white-space: normal !important;'>
                {$i->getType()}: <BR><b>{$i->getName()}</b>
            </td>
            <td class='d-none' style='font-size: 18px; white-space: normal !important;'>
                Quantity: <b class='hiddenCartQty'>{$quantity}</b><br>Location: <span style='font-weight: bold;'>{$part->getLocation()}</span><br>In-Stock: <span class='hidden-inventory-stock'>{$part->getQuantity()}</span>
            </td>
        </tr>";
	}
    
    $table .= '</tbody>
        </table>
        <script>
            function removeInventoryStock(button, stocknumber){
                const row = button.closest("tr");
                const stockData = row.querySelector(".inventory-stock"); 
                const quantity = Number(stockData.innerText);
                
                const qtyInput = row.querySelector(".current-quantity"); 
                let removeQty = Math.min(Number(qtyInput.innerText), quantity);

                updateInventoryQuantityByAmount(stocknumber, -removeQty);

                // Redundant if window reloads, but some refreshes dont refresh the page due to caching
                const newInventoryQty = Number(quantity) - Number(removeQty);	
                stockData.innerText = newInventoryQty;

                const hiddenStockData = row.querySelector(".hidden-inventory-stock");
                hiddenStockData.innerText = newInventoryQty;
            }

            function updateInventoryQuantityByAmount(id, amount){
                let content = {
                    action: "updateInventoryQuantityByAmount",
                    stockNumber: id,
                    amount: amount
                }
                
                api.post("/inventory.php", content).then(res => {
                    snackbar(res.message, "info");
                }).catch(err => {
                    snackbar(err.message, "error");
                });
            }
        </script>
    ';

    return $table;
}


/**
 * Generates the JavaScript for initializing a datatable that includes a print button for
 * printing onto receipt paper.
 * 
 * @param string $receiptTitle The title to display before the ID (e.g. "Cart Code")
 * @param string $columns The datatable definition for columns in the table (e.g.
 *                        indicating which ones cannot be used for sorting)
 * @param string $printColumns A JS array (stringified) representing indicating which
 *                             columns of the table should be included for printing
 * @return string
 */
function createReceiptDatatable($receiptTitle, $columns, $printColumns) {
    return <<< JS
    var printButtonExtension = {
        text: '<i class="fas fa-print"></i> Print Items',
        exportOptions: {
            columns: $printColumns,
            modifier: {
                search: 'applied', // only export filtered rows
                order: 'applied'   // respect current sort order
            },
            format: {
                body: (data, _, _2, node) => (
                    node.firstChild.tagName === "INPUT"
                        ? node.firstElementChild.value
                        : data
                )
            }
        }
    };

    const printThickness = 4; // Thickness in in
    const margin = 0.25; // Margin in in

    \$('#InventoryTable').DataTable({
        'dom': ((window.innerWidth < 768) ? 't' : 'Bft'),
        buttons: [
            \$.extend(true, {}, printButtonExtension, {
                extend: 'print',
                className: 'btn btn-sm btn-outline-secondary',
                init: function (_, node, config) {
                    \$(node).removeClass();
                    \$(node).addClass(config.className);
                },
                action: function (e, dt, button, config) {
                    // Custom logic before the print action
                    dt.rows().invalidate('dom').draw();
                    // Call the default print action
                    \$.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                },
                customize: function (win) {
                    // Remove the automatically added <h1> title
                    \$(win.document.body).find('h1').remove();

                    // Add heading with cart code and date, aligned with table
                    var urlParams = new URLSearchParams(window.location.search);
                    var id = urlParams.get('id') || '';
                    var today = new Date();
                    var dateString = today.getFullYear() + '-' + String(today.getMonth()+1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
                    var timeString = String(today.getHours()).padStart(2, '0') + ':' + String(today.getMinutes()).padStart(2, '0');
                    var headingHtml = `<div style="font-size:16pt; font-weight:bold; margin-top:\${margin}in; width:\${printThickness}in; margin-left:\${margin}in; margin-right:\${margin}in; text-align:center;">$receiptTitle: \${id} &nbsp; | &nbsp; \${dateString} \${timeString}</div>`;
                    \$(win.document.body).prepend(headingHtml);

                    // Set column widths
                    \$(win.document.body).find('table tr td.item-print-col, table tr th.item-print-col')
                        .css('width', printThickness*0.6+'in');
                    \$(win.document.body).find('table tr td.info-print-col, table tr th.info-print-col')
                        .css('width', printThickness*0.4+'in');

                    // Force table to 4in wide, centered
                    \$(win.document.body).find('table')
                        .css('table-layout', 'fixed !important')
                        .css('width', printThickness+'in')
                        .css('margin', `0 \${margin}in \${margin}in \${margin}in`);

                    //shrink text to fit
                    \$(win.document.body).find('table td, table th')
                        .css('white-space', 'pre-wrap')
                        .css('word-wrap', 'break-word');
                }
            })
        ],
        initComplete: function () {
            // Make search input match Bootstrap style
            const searchInput = \$(this.DataTable().table().container()).find('div.dataTables_filter');
            searchInput.addClass('form-inline mb-2');
            searchInput.find('input').addClass('form-control form-control-sm');
        },
        "autoWidth": true,
        'scrollX': false, 
        'paging': false, 
        'order': [[1, 'asc']],
        "columns": $columns
    });
JS;
}
