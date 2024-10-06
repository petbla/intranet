      <footer>
      </footer>
    </body>
</html>

<script>
    async function printToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'pt', 'a4');
            const pageHeight = doc.internal.pageSize.height;
            const pageWidth = doc.internal.pageSize.width;
            const margin = 40; // Nastavení okraje na 40px
            const usablePageHeight = pageHeight - 2 * margin;

            // Vyberte HTML element, který chcete převést do PDF
            const element = document.getElementById('Content');

            // Použijte html2canvas pro vytvoření screenshotu elementu
            const canvas = await html2canvas(element);
            const imgData = canvas.toDataURL('image/png');

            // Vypočítejte výšku obrazu v PDF
            const imgProps = doc.getImageProperties(imgData);
            const pdfWidth = pageWidth - 2 * margin;
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

            // Rozdělení obrázku na více stránek
            let yOffset = 0;
            while (yOffset < pdfHeight) {
                doc.addImage(imgData, 'PNG', margin, margin - yOffset, pdfWidth, pdfHeight ) ;
                yOffset += usablePageHeight;
                if (yOffset < pdfHeight) {
                    doc.addPage();
                }
            }

            doc.save('output.pdf');
    }
</script>
