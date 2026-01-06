<div class="price_card card mb-3 p-3 rounded-2 box-shadow">
    <div class="card-body px-0 py-2">
        <h5 class="text-primary-orange">Durability, Packaging and Supply Chain</h5>
        <table class="table table-borderless nutrition_table nutritional-analysis">
            <thead>
                <tr>
                    <th></th>
                    <th></th>      
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" class="primary-text-dark fw-bold">As supplied (unopened pack or bulk)</td>
                </tr>
                <tr>
                    <td>Shelf Life:</td>
                    <td>@if($prodLabel && $prodLabel->rm_supplied_shelf_life_num) {{$prodLabel->rm_supplied_shelf_life_num}} {{$prodLabel->rm_supplied_shelf_life_units}}@else NA @endif</td>
                </tr>
                <tr>
                    <td>Temperature controlled during storage:</td>
                    <td>@if($prodLabel && $prodLabel->rm_suppied_temp_control_storage_num) {{$prodLabel->rm_suppied_temp_control_storage_num}}@else NA @endif</td>
                </tr>
                <tr>
                    <td>Temperature controlled during transport:</td>
                    <td>@if($prodLabel && $prodLabel->rm_suppied_temp_control_storage_degrees) {{$prodLabel->rm_suppied_temp_control_storage_degrees}}@else NA @endif</td>
                </tr>
                <tr>
                    <td colspan="2" class="primary-text-dark fw-bold">Product - Once in Use (resealable pack or bulk container)</td>
                </tr>
                <tr>
                    <td>Shelf Life:</td>
                    <td>@if($prodLabel && $prodLabel->rm_inuse_shelf_life_num) {{$prodLabel->rm_inuse_shelf_life_num}} {{$prodLabel->rm_inuse_shelf_life_units}} @else NA @endif</td>
                </tr>
                <tr>
                    <td>Temperature controlled during storage:</td>
                    <td>@if($prodLabel && $prodLabel->rm_inuse_temp_control_storage_degrees) {{$prodLabel->rm_inuse_temp_control_storage_degrees}}@else NA @endif</td>
                </tr>
                <tr>
                    <td colspan="2" class="primary-text-dark fw-bold">Other</td>
                </tr>
                <tr>
                    <td>Specifiy any other storage requirements:</td>
                    <td>@if($prodLabel && $prodLabel->rm_storage_requirement) {{$prodLabel->rm_storage_requirement}}@else NA @endif</td>
                </tr>
                <tr>
                    <td>Indended use:</td>
                    <td>@if($prodLabel && $prodLabel->rm_indended_use) {{$prodLabel->rm_indended_use}}@else NA @endif</td>
                </tr>
                <tr>
                    <td>Specifiy type of date mark to be used:</td>
                    <td>@if($prodLabel && $prodLabel->rm_date_mark) {{$prodLabel->rm_date_mark}}@else NA @endif</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>