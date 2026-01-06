<!DOCTYPE html>
<html>
    <meta charset="UTF-8">
    @php 
    
    @endphp
    <head>
        <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:wght@100&display=swap" rel="stylesheet">
        <style>
            @if($type == 'black')
                @php 
                    $hex = "#000000";
                    list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
                    $bg = "rgba($r, $g, $b, 0.1)";
                @endphp 
                :root {
                    --primary-color: #000000;
                    --secondary-color: #333333;
                    --Primary-dark-Mud: #111111;
                    --Primary-white-Mud: #FFFFFF;
                    --Primary-background: {{$bg}};
                }
            @elseif($type == 'custom')
                @php 
                    $hex = $colors['primaryColor'] ?? "#328678";
                    list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
                    $bg = "rgba($r, $g, $b, 0.2)";
                @endphp
                :root {
                    --primary-color: {{$colors['primaryColor'] ?? "#328678"}};
                    --secondary-color: {{$colors['secondaryColor'] ?? "#009FFF"}};
                    --Primary-dark-Mud: #403E3E;
                    --Primary-white-Mud: #FEF8F9;
                    --Primary-background: {{$bg}};
                }
            @endif

            @page {margin: 160px 0px;}
            body{font-family: 'Poppins',Arial, sans-serif }
            h1, h2, h3, h4{font-family: "Montserrat", Arial, sans-serif;font-weight: 700;font-style: normal;line-height: normal;}
            p{font-family: "Poppins", Arial, sans-serif; font-size:11px;font-weight:normal;}
            ol,li{font-size:11px;font-weight:normal;}
            h1{font-size: 32px; color: var(--primary-color);}
            h2{font-size: 24px; color: var(--primary-color);}
            h3{font-size: 17px; color: var(--secondary-color); margin-bottom:3px;}
            h4{font-size: 16px; color: var(--Primary-dark-Mud);} 
            header { position: fixed; left: 0px; right: 0px; height: 80px; margin-top: -160px;background-color: var(--Primary-background);}
            footer { position: fixed;  left: 0px; right: 0px; bottom: -160px;background-color: var(--Primary-background); width: 100%; padding: 0px 25px;}
            header .page-title {position: absolute; left: 30px; top: 50%; transform: translateY(-50%); width: 70%; margin: 0px; color:var(--Primary-dark-Mud);}
            .client-logo{position: absolute; right: 10px; top: 15px; width: 30%; text-align: right;}
            .client-logo img{max-width:60%;  max-height: 60px; object-fit: contain;}
            main{margin: 0px 25px; margin-top: -60px;margin-bottom: -50px;}
            .footer_wrapper{margin: 0 25px;}
            .main-left-section{width: 80%; float: left;}
            .main-right-section{width: 20%; float: right;}
            .details-wrapper{clear: both;margin-top:5px;}
            .details-left-section{width: 50%; float: left;}
            .details-right-section{width: 50%; float: right;}
            .product-information p, .nutrition-wrapper p{font-size: 12px;}
            .product-image-section img{max-width:100%;object-fit: contain;border-radius: 1rem;}
            p.footer-official-details{color: var(--primary-color);font-size: 12px;font-weight: 600;}
            .fw-bold {font-weight: 700;}
            .nutrition-table {width: 100%;border-collapse: collapse;font-family: 'Montserrat', Arial, sans-serif;font-size: 11px;color: #333;}
            .nutrition-table th,.nutrition-table td,.footer-table th,.footer-table td {padding: 5px 7px;text-align: left;}
            .nutrition-table thead th,.footer-table thead th {border-bottom: 1px solid #ccc;font-weight: bold;}
            .nutrition-table tbody td, .footer-table tbody td{border-bottom: 1px solid #e0e0e0;}
            .footer-table {width: 100%;border-collapse: collapse;font-family: 'Montserrat', DejaVu Sans, sans-serif;font-size: 10px;color: #333;}
            .page-number:after {content: "Page " counter(page) " of " counter(pages);}
            p.prod_sku{color:var(--primary-color);font-weight: 700;}
            .recipe-notes {font-family: "Poppins", Arial, sans-serif; font-size:11px;font-weight:normal;}
            .bottom-section {position: absolute;bottom: 120px;left: 25px;right: 25px;page-break-inside: avoid;}
            .text-end{text-align:right !important;}
        </style>
    </head>
    <body>
        <header>
            <div class="page-title">
                <h1>Recipe Card</h1> 
            </div>
            <div class="client-logo">
                <img src="{{ $client_logo }}" alt="client-logo">
            </div>
        </header>

        <footer>
            <table>
                <tr>
                    <td style="width:80%;">
                        <p style="font-size:10px; margin-bottom:0px;margin-right:40px;">DISCLAIMER: Specification may vary without notice. The product contained in this specification is based on data considered to be accurate and reliable as at the date of the specification.{{$product->productClient->company_name}} {{date("Y")}}. All Rights Reserved. This recipe is confidential and the intellectual property of {{$product->productClient->company_name}}.
                        </p>
                         <table style="width: 100%; font-size:8px; margin-top: 5px;">
                            <tr>
                                <td style="width: 33%; text-align: center;">
                                    {{ $product->productClient->company_name }}<br>
                                    ABN {{$ABN}}
                                </td>
                                <td style="width: 33%; text-align: center;">
                                    {{ $product->productClient->address }}<br>
                                    {{ $product->productClient->city }}, {{ $product->productClient->state }}, {{ $product->productClient->zip_code }}<br>
                                    {{ $product->productClient->phone_number }}
                                </td>
                                <td style="width: 33%; text-align: center;">
                                    {{ $product->productClient->company_email }}<br>
                                    Private and Confidential
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:20%;">
                        <img src="{{$batchbase_logo}}" alt="Batchbase Logo">
                    </td>
            </tr>
            </table>
        </footer>

        <main>
            <div class="main-section-wrapper">  
                <div class="main-left-section">
                    <div class="product-information">
                        <h3>{{ $product->prod_name }}</h3>  
                        <p class="prod_sku">{{ $product->prod_sku }}</p>
                        @if($product->barcode_gs1)<p>{{ $product->barcode_gs1 }}</p>@endif
                        @if($product->barcode_gtin14)<p>{{ $product->barcode_gtin14 }}</p>@endif
                        @if($product->description_short && strip_tags($product->description_short)!='')
                            {!! format_content($product->description_short) !!}
                        @endif
                    </div>
                </div>
                <div class="main-right-section">
                    <div class="product-image-section">
                        <img src="{{ $product_image }}" alt="Recipe Image" class="product_image img-fluid rounded">
                    </div>  
                </div>
            </div>

            <div class="details-wrapper style="margin-top:10px;">
                <div class="details-left-section">
                    <div class="nutrition-wrapper" style="margin-right:15px;">
                        <h3>Ingredients</h3>  
                        <table class="table nutrition-table">
                            <thead>
                                <tr style="border-bottom: 1px solid #e0e0e0; font-weight:bold;">
                                    <td>Component</td>
                                    <td>Name</td>
                                    <td class="text-end">Quantity(g)</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedIngredients as $component => $ingredients)
                                    @foreach($ingredients as $ingredient)
                                        <tr>
                                            <td>{{ ucfirst($component) }}</td>
                                            <td>{{ $ingredient->ing_name }}</td>
                                            <td class="text-end">{{ number_format($ingredient->quantity_g, 0) }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach  
                                <tr>
                                    <td colspan="2" style="border-top: 2px solid #050505ff">Batch Weight <strong>Before</strong> Gain or loss (g)</td>
                                    <td class="text-end" style="border-top: 2px solid #050505ff">{{ number_format($batchTotal, 0) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2">Batch Weight <strong>After</strong> Gain or loss (g)</td>
                                    <td class="text-end">{{ number_format($product->batch_after_waste_g, 0) }}</td>  
                                </tr>
                                <tr>
                                    <td colspan="2">Weight Gain or Loss % </td>
                                    <td class="text-end">{{ $product->batch_baking_loss_percent }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="details-right-section">
                    <div class="product-information-section" style="margin-left:15px;">
                        <h3>Oven Temperature & Time</h3>  
                        <table class="table footer-table">
                            <tr>
                                <td>Oven Temperature (°C)</td>
                                <td>{{ $product->recipe_oven_temp }}</td>
                            </tr>
                            <tr>
                                <td>Oven Time (hh:mm:ss)</td>
                                <td>{{ $product->formatted_oven_time  }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="product-information-section" style="margin-left:15px;">
                        <h3>Recipe Method</h3>  
                        <div class="recipe-notes">
                            @if($product->recipe_method && strip_tags($product->recipe_method) != '')
                                {!! format_content($product->recipe_method) !!}
                            @else
                                <p>Recipe method not available.</p>
                            @endif
                        </div>
                    </div>

                    <div class="product-information-section" style="margin-left:15px;">
                        <h3>Recipe Notes</h3>  
                        <div class="recipe-notes">
                            
                            @if($product->recipe_notes && strip_tags($product->recipe_notes) != '')
                                {!! format_content($product->recipe_notes) !!}
                            @else
                                <p>Recipe notes not available.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bottom-section">
                <div class="details-wrapper">
                    <div class="details-left-section">
                        <div class="nutrition-wrapper" style="margin-right:15px;">
                            <h3>Units & Price</h3>  
                            <table class="table nutrition-table">
                                <thead>
                                    <tr style="border-bottom: 1px solid #e0e0e0; font-weight:bold;">
                                        <td></td>
                                        <td class="text-end">Ind Unit</td>
                                        <td class="text-end">Sell Unit</td>
                                        <td class="text-end">Carton</td>
                                        <td class="text-end">Pallet</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Weight (g)</td>
                                        <td class="text-end">{{ ($product->weight_ind_unit_g) ? round($product->weight_ind_unit_g,1): 100 }}</td>
                                        <td class="text-end">{{ ($product->weight_retail_unit_g) ? round($product->weight_retail_unit_g,1): N/A }}</td>
                                        <td class="text-end">{{ ($product->weight_carton_g) ? round($product->weight_carton_g,1): N/A }}</td>
                                        <td class="text-end">{{ ($product->weight_pallet_g) ? round($product->weight_pallet_g,1): N/A }}</td>
                                    </tr>
                                    <tr>
                                        <td>Sell Unit (#)</td>
                                        <td></td>
                                        <td class="text-end">{{ ($product->count_ind_units_per_retail) ? round($product->count_ind_units_per_retail,1): 0 }}</td>
                                        <td class="text-end">{{ ($product->count_retail_units_per_carton) ? round($product->count_retail_units_per_carton,1): 0 }}</td>
                                        <td class="text-end">{{ ($product->count_cartons_per_pallet) ? round($product->count_cartons_per_pallet,1): 0 }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="details-right-section">
                        <div class="nutrition-wrapper" style="margin-left:15px;">
                            <h3>Official Details</h3>
                            <table class="table nutrition-table">
                                <tr>
                                    <td>Manufacturing Location</td>
                                    <td>{{ $product->factoryAddress ? $product->factoryAddress : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td>Compliance Officer</td>
                                    <td>{{ $product->keyPerson ? $product->keyPerson : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td>Date</td>
                                    <td>{{ date('d M Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </main>
    </body>
</html>

