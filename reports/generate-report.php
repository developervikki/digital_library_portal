<?php    

require_once('../includes/db.php');  
  
 
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
    
// Simulated login — replace with $_SESSION 
$student_id = 1; 

// Fetch student sessions
$stmt = $conn->prepare("SELECT start_time, end_time, duration FROM table_time_logs WHERE student_id = ? ORDER BY start_time DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// Start PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Digital Library');
$pdf->SetTitle('Study Time Report');
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// Title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '📘 Study Time Report', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Student ID: ' . $student_id, 0, 1);
$pdf->Ln(5);

// Table headers
$html = '
<table border="1" cellpadding="5">
<tr style="background-color:#e0f0ff;">
  <th width="30%">Start Time</th>
  <th width="30%">End Time</th>
  <th width="20%">Duration</th>
</tr>';

// Add table rows
$total = 0;
while ($row = $result->fetch_assoc()) {
    $start = date("d M Y, h:i A", strtotime($row['start_time']));
    $end = date("d M Y, h:i A", strtotime($row['end_time']));
    $duration = gmdate("H:i:s", $row['duration']);
    $total += $row['duration'];

    $html .= "
    <tr>
        <td>$start</td>
        <td>$end</td>
        <td align='center'>$duration</td>
    </tr>";
}

$html .= '</table><br><br>';

// Total
$html .= '<strong>Total Study Time: </strong> ' . gmdate("H:i:s", $total);

// Output PDF
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('study_report.pdf', 'I');
exit;
