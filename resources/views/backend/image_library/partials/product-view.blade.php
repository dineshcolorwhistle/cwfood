@php
use Carbon\Carbon;
@endphp
@if($viewType == 'list')
<table class="table" id="dtRecordsView">
    <thead>
        <th style="width:4%;"></th>
        <th style="width:6%;"></th>
        <th style="width:90%;"></th>
    </thead>
    <tbody id="resultsBody">
        @foreach($lists as $key => $list)
        <tr class="search_table_row" id="img_lib">
            <td class="align-middle">
                <input class="form-check-input" name="img_ckeck" type="checkbox" value="" data-module="ingredient" data-moduleid="{{$list['id']}}" id="img_ckeck{{$key}}">
            </td>
            <td class="align-middle">
                @php
                $imgUrl = '';
                if($list['ing_image']){
                $imgUrl = get_default_image_url('raw_material',$list['ing_image'],$list['id']);
                }else{
                $imgUrl = "/dev/assets/img/prod_default.png";
                }
                @endphp
                <img src="{{env('APP_URL')}}{{$imgUrl}}" alt="Product Image" class="product-thumbnail image-lib" style="max-width: 65%;">
            </td>
            <td class="align-middle">
                @if($list['ing_image'])
                @php
                $img_details = get_default_image_details('raw_material',$list['ing_image'],$list['id']);
                $formattedTime = (isset($img_details['updated_at'])) ? Carbon::parse($img_details['updated_at'])->format('D F d,Y H:i'):'';
                @endphp
                <div class="image_details_wrapper">
                    <a href="{{route('show.images',['id'=>$list['id']])}}">
                        <h5 class="mb-1 text-dark-mud" style="font-weight: 600;">ING_{{$img_details['image_name']}}</h5>
                    </a>
                    <p class="text-primary-dark-mud">{{$formattedTime}}, {{$img_details['file_size']}},{{get_user_name($img_details['last_modified_by'])}}</p>
                </div>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@else

@endif