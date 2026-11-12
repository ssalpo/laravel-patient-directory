<!doctype html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Карточка пациента</title>

    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            color: #212529;
            font-size: 14px;
        }

        .table.table-bordered td {
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .editor-content p {
            margin-top: 0;
            margin-bottom: 7px;
            line-height: 16px;
        }
    </style>
</head>
<body>

<table width="100%" style="margin-bottom: 50px">
    <tr>
        <td width="50%" style="text-align: center; font-size: 16px">
            Городской клинический центр <br>
            кожных болезней
        </td>
        <td style="text-align: center; font-size: 16px">
            Маркази клиникавии шахрии <br> беморихои пуст
        </td>
    </tr>
</table>

<table border="1" width="100%" cellpadding="10" cellspacing="0" class="table table-bordered">
    <tbody>
    <tr>
        <td width="200">ФИО пациента</td>
        <td>{{$patient->name}}</td>
    </tr>
    <tr>
        <td width="200">Место проживания</td>
        <td>{{$patient->location?->full_address ?? $patient->place_of_residence}}</td>
    </tr>
    <tr>
        <td>Дата рождения</td>
        <td>{{$patient->birthday->format('d.m.Y')}}</td>
    </tr>
    <tr>
        <td>Пол</td>
        <td>{{$patient->gender ? 'М' : 'Ж'}}</td>
    </tr>
    <tr>
        <td>Номер медицинской записи</td>
        <td>-</td>
    </tr>
    <tr>
        <td>Дата/время забора образца</td>
        <td>{{$patient->sampling_date}}</td>
    </tr>
    <tr>
        <td>Дата/время получения образца</td>
        <td>{{$patient->sample_receipt_date}}</td>
    </tr>
    <tr>
        <td>Номер кейса</td>
        <td>
            @foreach($patient->case_numbers ?? [] as $caseNumber)
                <div>{{$caseNumber}}</div>
            @endforeach
        </td>
    </tr>
    </tbody>
</table>

<div v-if="$page.props.shared.userPermissions.includes('read_histopathology')">
<h4 style="font-weight: normal; font-size: 16px; margin-top: 40px">Гистопатологическое заключение</h4>

<table class="table table-bordered" width="100%" cellpadding="10" cellspacing="0">
    <tbody>
    <tr>
        <td width="200">Тип/место забора образца</td>
        <td>
            {{implode(', ', $patient->categories_formatted)}}
        </td>
    </tr>
    <tr>
        <td>Микроскопическое описание</td>
        <td class="editor-content">
            @if($patient->isReportReady())
                {!! $patient->microscopic_description !!}
            @endif
        </td>
    </tr>
    <tr>
        <td><b>Диагноз</b></td>
        <td class="editor-content">
            @if($patient->isReportReady())
                {!! $patient->diagnosis !!}
            @endif
        </td>
    </tr>
    <tr>
        <td>Заметка</td>
        <td class="editor-content">
            @if($patient->isReportReady())
                {!! $patient->note !!}
            @endif
        </td>
    </tr>
    </tbody>
</table>
</div>

<table style="margin-top: 30px" width="100%">
    <tr>
        <td width="50%" style="text-align: right">Врач дерматопатолог:</td>
        <td width="15%"></td>
        <td width="50%">Султонов Р. А.</td>
    </tr>
    <tr>
        <td style="text-align: right">Дата:</td>
        <td></td>
        <td>{{$patient->print_date?->format('d.m.Y') ?? $patient->created_at->format('d.m.Y')}}</td>
    </tr>
</table>

</body>
</html>
