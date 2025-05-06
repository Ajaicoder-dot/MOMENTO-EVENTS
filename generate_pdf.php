<?php
// Update the path to where TCPDF is actually installed
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
// If you installed it in a different location, adjust the path accordingly
// For example: require_once('path/to/tcpdf/tcpdf.php');
include 'db.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Booking ID is required");
}

$booking_id = $_GET['id'];

// Fetch booking details
$sql = "SELECT 
    id,
    event_head, 
    phone_no, 
    guest_email, 
    guest_address, 
    event_start_date, 
    event_end_date, 
    event_start_time, 
    event_end_time, 
    venue, 
    selected_services, 
    total_amount, 
    additional_details, 
    cancelled, 
    cancel_reason, 
    approval_status, 
    rejection_reason 
FROM book WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found");
}

$booking = $result->fetch_assoc();

// Function to parse JSON services (similar to the one in manage_booking.php)
function parseServicesForPdf($jsonString) {
    $parsed = json_decode($jsonString, true);
    
    if ($parsed === null) {
        return $jsonString;
    }
    
    $services = [];
    foreach ($parsed as $service) {
        if (isset($service['name']) && isset($service['amount'])) {
            $services[] = $service['name'] . ' - ' . $service['amount'];
        } elseif (isset($service['service_name']) && isset($service['service_price'])) {
            $services[] = $service['service_name'] . ' - ' . $service['service_price'];
        }
    }
    
    return implode("\n", $services);
}

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Booking System');
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle('Booking Details - ' . $booking['event_head']);
$pdf->SetSubject('Booking Confirmation');

// Remove header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(15, 15, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Booking title
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 10, 'Booking Confirmation', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Booking ID: ' . $booking['id'], 0, 1, 'C');
$pdf->Ln(10);

// Status
$status = $booking['cancelled'] == 1 ? 'Cancelled' : $booking['approval_status'];
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Status: ' . ucfirst($status), 0, 1);
$pdf->Ln(5);

// Guest Information
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Guest Information', 0, 1);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(50, 8, 'Event Head:', 0);
$pdf->Cell(0, 8, $booking['event_head'], 0, 1);
$pdf->Cell(50, 8, 'Phone Number:', 0);
$pdf->Cell(0, 8, $booking['phone_no'], 0, 1);
$pdf->Cell(50, 8, 'Email:', 0);
$pdf->Cell(0, 8, $booking['guest_email'], 0, 1);
$pdf->Cell(50, 8, 'Address:', 0);
$pdf->MultiCell(0, 8, $booking['guest_address'], 0, 'L');
$pdf->Ln(5);

// Event Schedule
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Event Schedule', 0, 1);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(50, 8, 'Start Date & Time:', 0);
$pdf->Cell(0, 8, $booking['event_start_date'] . ' ' . $booking['event_start_time'], 0, 1);
$pdf->Cell(50, 8, 'End Date & Time:', 0);
$pdf->Cell(0, 8, $booking['event_end_date'] . ' ' . $booking['event_end_time'], 0, 1);
$pdf->Ln(5);

// Venue
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Venue', 0, 1);
$pdf->SetFont('helvetica', '', 12);
$pdf->MultiCell(0, 8, $booking['venue'], 0, 'L');
$pdf->Ln(5);

// Services
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Services', 0, 1);
$pdf->SetFont('helvetica', '', 12);
$services = parseServicesForPdf($booking['selected_services']);
$pdf->MultiCell(0, 8, $services, 0, 'L');
$pdf->Ln(5);

// Total Amount
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Payment', 0, 1);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(50, 8, 'Total Amount:', 0);
$pdf->Cell(0, 8, '₹' . $booking['total_amount'], 0, 1);
$pdf->Ln(5);

// Additional Details
if (!empty($booking['additional_details'])) {
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Additional Details', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, $booking['additional_details'], 0, 'L');
    $pdf->Ln(5);
}

// Cancellation/Rejection Reason
if ($booking['cancelled'] == 1 && !empty($booking['cancel_reason'])) {
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Cancellation Reason', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, $booking['cancel_reason'], 0, 'L');
}

if (!empty($booking['rejection_reason'])) {
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Rejection Reason', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, $booking['rejection_reason'], 0, 'L');
}

// Output the PDF
$pdf->Output('booking_' . $booking['id'] . '.pdf', 'D');

// Close database connection
$conn->close();
?>