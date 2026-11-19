from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_RIGHT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import mm
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle

OUTPUT = r"C:\laragon\www\sebutharga\output\pdf\contoh-local-order-pelanggan.pdf"
styles = getSampleStyleSheet()
styles.add(ParagraphStyle(name="Small", parent=styles["Normal"], fontName="Helvetica", fontSize=8.5, leading=11))
styles.add(ParagraphStyle(name="SmallBold", parent=styles["Small"], fontName="Helvetica-Bold"))
styles.add(ParagraphStyle(name="TitleCenter", parent=styles["Title"], fontName="Helvetica-Bold", fontSize=15, leading=18, alignment=TA_CENTER, spaceAfter=5))
styles.add(ParagraphStyle(name="Right", parent=styles["Small"], alignment=TA_RIGHT))

def p(text, style="Small"):
    return Paragraph(text, styles[style])

doc = SimpleDocTemplate(OUTPUT, pagesize=A4, rightMargin=16 * mm, leftMargin=16 * mm, topMargin=14 * mm, bottomMargin=14 * mm)
story = []
story.append(p("KADA", "TitleCenter"))
story.append(p("Lembaga Kemajuan Pertanian Kemubu", "SmallBold"))
story.append(p("Peti Surat 127, Jalan Dato' Lundang, 15710 Kota Bharu, KELANTAN"))
story.append(Spacer(1, 8))
story.append(p("PESANAN TEMPATAN", "TitleCenter"))
story.append(p("LOCAL ORDER", "SmallBold"))
story.append(Spacer(1, 7))

meta = Table([
    [p("Tahun Kewangan", "SmallBold"), p("2026"), p("No. Pesanan", "SmallBold"), p("26001624")],
    [p("Tarikh Pesanan", "SmallBold"), p("27/07/2026"), p("Status", "SmallBold"), p("PENIGA")],
], colWidths=[29 * mm, 58 * mm, 29 * mm, 58 * mm])
meta.setStyle(TableStyle([("GRID", (0, 0), (-1, -1), 0.5, colors.HexColor("#777777")), ("BACKGROUND", (0, 0), (0, -1), colors.HexColor("#eeeeee")), ("BACKGROUND", (2, 0), (2, -1), colors.HexColor("#eeeeee")), ("VALIGN", (0, 0), (-1, -1), "TOP"), ("LEFTPADDING", (0, 0), (-1, -1), 6), ("RIGHTPADDING", (0, 0), (-1, -1), 6), ("TOPPADDING", (0, 0), (-1, -1), 6), ("BOTTOMPADDING", (0, 0), (-1, -1), 6)]))
story.append(meta)
story.append(Spacer(1, 9))

recipient = Table([
    [p("Pembekal / Kontraktor", "SmallBold"), p("V0004385 - IF RESOURCES SDN BHD<br/>22, Tingkat 2, Jalan Long Yunus<br/>15050 Kota Bharu, Kelantan")],
    [p("Tempat Hantar", "SmallBold"), p("Kumpulan Wang Mengurus<br/>Peti Surat 127, Jalan Dato' Lundang<br/>15710 Kota Bharu, Kelantan")],
    [p("No. Sebut Harga", "SmallBold"), p("K1R-QT-2026-003")],
], colWidths=[29 * mm, 145 * mm])
recipient.setStyle(TableStyle([("GRID", (0, 0), (-1, -1), 0.5, colors.HexColor("#777777")), ("BACKGROUND", (0, 0), (0, -1), colors.HexColor("#eeeeee")), ("VALIGN", (0, 0), (-1, -1), "TOP"), ("LEFTPADDING", (0, 0), (-1, -1), 6), ("RIGHTPADDING", (0, 0), (-1, -1), 6), ("TOPPADDING", (0, 0), (-1, -1), 6), ("BOTTOMPADDING", (0, 0), (-1, -1), 6)]))
story.append(recipient)
story.append(Spacer(1, 9))

items = Table([
    [p("Bil.", "SmallBold"), p("Butiran", "SmallBold"), p("Kuantiti", "SmallBold"), p("Harga Seunit (RM)", "SmallBold"), p("Jumlah (RM)", "SmallBold")],
    [p("1"), p("PERKHIDMATAN PENYELENGGARAAN SISTEM I-PADI BAGI MUSIM 2/2025 DAN PENAMBAHBAIK KEPERLUAN MODUL PENDAFTARAN SISTEM I-PADI UNTUK 13 PPK<br/><br/>Kod barang: M-09-0000-0000-B28601"), p("1.00", "Right"), p("18,500.0000", "Right"), p("18,500.00", "Right")],
    ["", "", "", p("JUMLAH BESAR", "SmallBold"), p("18,500.00", "Right")],
], colWidths=[12 * mm, 88 * mm, 20 * mm, 27 * mm, 27 * mm], repeatRows=1)
items.setStyle(TableStyle([("GRID", (0, 0), (-1, -1), 0.5, colors.HexColor("#777777")), ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#dddddd")), ("SPAN", (0, 2), (2, 2)), ("ALIGN", (0, 0), (-1, -1), "CENTER"), ("ALIGN", (1, 1), (1, 1), "LEFT"), ("ALIGN", (3, 1), (-1, -1), "RIGHT"), ("VALIGN", (0, 0), (-1, -1), "TOP"), ("LEFTPADDING", (0, 0), (-1, -1), 5), ("RIGHTPADDING", (0, 0), (-1, -1), 5), ("TOPPADDING", (0, 0), (-1, -1), 7), ("BOTTOMPADDING", (0, 0), (-1, -1), 7)]))
story.append(items)
story.append(Spacer(1, 10))
story.append(p("Nota: Ini ialah contoh PDF berdasarkan LO KADA yang diberikan. LO sebenar hendaklah dimuat naik sebagai dokumen pelanggan tanpa mengubah kandungan asal."))
story.append(Spacer(1, 18))

signatures = Table([
    [p("Disediakan oleh, " , "SmallBold"), p("Disahkan / Diterima oleh,", "SmallBold")],
    [p("______________________________\nNama / Jawatan / Tarikh"), p("______________________________\nNama / Jawatan / Tarikh")],
], colWidths=[87 * mm, 87 * mm])
signatures.setStyle(TableStyle([("VALIGN", (0, 0), (-1, -1), "TOP"), ("TOPPADDING", (0, 0), (-1, -1), 5), ("BOTTOMPADDING", (0, 0), (-1, -1), 5)]))
story.append(signatures)
doc.build(story)
print(OUTPUT)
