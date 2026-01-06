<div class="price_card menu-bg mb-3 p-3 rounded-2 box-shadow">
    <div class="card-body px-0 py-2">
        <h5 class="text-primary-orange">Direct costs ($/kg)</h5>
        <table class="table table-borderless">
            <tbody>
                @foreach(['ingredient', 'packaging', 'machinery', 'labour', 'total', 'contingency', 'total_direct'] as $costType)
                <tr @if($costType == "labour" || $costType == "contingency" ) style="border-bottom: 1px solid #000;" @endif>
                    <td>
                        {{ 
                            $costType === 'total_direct' ? 'Overall Total' : 
                            ($costType === 'total' ? 'Total' : ucfirst($costType) . ':') 
                        }}
                    </td>
                    <td class="text-end">{{ number_format($costingData[$costType]['per_kg'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
