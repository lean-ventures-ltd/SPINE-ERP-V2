<div class="tab-pane in" id="tab_data2" aria-labelledby="tab2" role="tabpanel">
    <div class="card">
        <div class="card-body">
            <table id="djcsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Report No.</th>
                        <th>Customer - Branch</th>
                        <th>Subject</th>
                        <th>JobCard</th>
                        <th>Client Ref</th>
                        <th>Ticket No</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    
                        @foreach ($djcs as $i => $djc)
                        @if (count($djc) > 0)
                        <tr>
                            @php
                                $link = '';
                                if ($djc->client) {
                                    $customer = $djc->client->company;
                                    if ($djc->branch) $customer .= " - {$djc->branch->name}";
                                    $link = $customer . ' <a class="font-weight-bold" href="'. route('biller.customers.show', $djc->client) .'"><i class="ft-eye"></i></a>';
                                }
                                if ($djc->lead && !$link) {
                                    $link = $djc->lead->client_name;
                                }
                                
                            @endphp
                            <td>{{$i+1}}</td>
                            <td>{{gen4tid("Tkt-", $djc->reference)}}</td>
                            <td>{{$client_name}}</td>
                            <td>{{$djc->subject}}</td>
                            <td>{{$link}}</td>
                            <td>{{$djc->job_card}}</td>
                            <td>{{$djc->created_at}}</td>
                            <td>{{$djc->client_ref}}</td>
                        </tr>
                            
                        @endif
                           
                        @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
</div>
