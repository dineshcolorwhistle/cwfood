@php
    use Carbon\Carbon;
    $statusArray = ['Received','In progress','Parked','Waiting for customer','Resolved'];
    $priorityArray = ['Highest','High','Medium','Low','Lowest'];
    $userID = Session::get('user_id');                   
@endphp

@foreach($tickets as $ticket)
    @php
        switch ($ticket['priority']) {
            case 'Highest':
                $priority_label = "inline-select-priority-Highest";
                break;
            case 'High':
                $priority_label = "inline-select-priority-High";
                break;
            case 'Medium':
                $priority_label = "inline-select-priority-Medium";
                break;
            case 'Low':
                $priority_label = "inline-select-priority-Low";
                break;
            case 'Lowest':
                $priority_label = "inline-select-priority-Lowest";
                break;
            default:
                $priority_label = "inline-select-priority-Medium";
                break;
        }

        switch ($ticket['status']) {
            case 'Received':
                $status_label = "inline-select-status-Received";
                break;
            case 'In progress':
                $status_label = "inline-select-status-InProgress";
                break;
            case 'Parked':
                $status_label = "inline-select-status-Parked";
                break;
            case 'Waiting for customer':
                $status_label = "inline-select-status-WaitforCustomer";
                break;
            case 'Resolved':
                $status_label = "inline-select-status-Resolved";
                break;
            default:
                $status_label = "inline-select-status-Received";
                break;
        }

    @endphp
    <tr class="edit-ticket" data-ticket="{{$ticket['id']}}">
        <td class="drag-handle" draggable="true"><span class="material-symbols-outlined">drag_indicator</span></td>
        <td class="text-primary-dark-mud">{{ $ticket['topic'] }}</td>
        <td class="text-primary-dark-mud">{{$ticket['assignee_details'] && $ticket['assignee_details']['name'] ? $ticket['assignee_details']['name'] : 'N/A' }}</td>
        <td class="text-primary-dark-mud">{{($ticket['requester_details'])? $ticket['requester_details']['name']: 'N/A' }}</td>
        <td class="text-primary-dark-mud">{{old('category', $ticket['category']?? 'N/A')}}</td>
        <td class="text-primary-dark-mud">{{old('time_spent', $ticket['time_spent']?? 'N/A')}}</td>
        <td class="text-primary-dark-mud">{{ ($ticket['due_date'])? Carbon::parse($ticket['due_date'])->format('j M Y'): 'N/A' }}</td>
        <td class="text-primary-dark-mud">
            <select name="sort_priority" class="form-control-select js-example-basic-single {{$priority_label}}" data-ticket="{{$ticket['id']}}" onchange="priority_update(this)">
                @foreach($priorityArray as $priority)
                <option value="{{$priority}}" @if($ticket['priority'] == $priority) selected @endif>{{$priority}}</option>
                @endforeach
            </select>
        </td>
        <td class="text-primary-dark-mud">
            <select name="sort_status" class="form-control-select js-example-basic-single {{$status_label}} status_update" data-prev="" data-ticket="{{$ticket['id']}}" data-timespend="{{$ticket['time_spent']}}" data-assignee="{{$ticket['assignee_details'] && $ticket['assignee_details']['name'] ? $ticket['assignee_details']['name'] : 'N/A' }}">
                @foreach($statusArray as $status)
                    <option value="{{$status}}" @if($ticket['status'] == $status) selected @endif>{{$status}}</option>
                @endforeach
            </select>
        </td>
        <td style="display:none;">{{ $ticket['sort_order'] }}</td>
        <td style="display:none;">{{ $ticket['client_details']['name']?? 'N/A' }}</td>
        <td style="display:none;">{{ $ticket['status'] }}</td>
        <td style="display:none;">{{ $ticket['priority'] }}</td>
        <td style="display:none;">{{ $ticket['assignee'] }}</td>
        <td><div class="d-flex"><span class="material-symbols-outlined delete-row-data" data-id="{{ $ticket['id'] }}">delete</span> <a href="{{ route('admin.view.ticket', ['ticket' => $ticket['id']]) }}" target="_blank"><span class="material-symbols-outlined" data-id="{{ $ticket['id'] }}">visibility</span></a></div></td>
    </tr>
@endforeach
