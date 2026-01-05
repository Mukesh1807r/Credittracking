<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
function downloadPDF() {
  const { jsPDF } = window.jspdf;   // ✅ correct way
  const pdf = new jsPDF();
  pdf.text("Academic Credit Report", 20, 20);
  pdf.text(
    document.getElementById("report").innerText,
    10,
    40
  );
  pdf.save("credit-report.pdf");
}
</script>
