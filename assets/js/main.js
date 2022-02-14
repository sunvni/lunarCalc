

(function() {
    let nowTime = new Date;
    let dateStr = nowTime.getFullYear() + "-" + ("0" + (nowTime.getMonth() + 1)).slice(-2) + "-" + ("0" + nowTime.getDate()).slice(-2);

    document.getElementById('ngay_lich_duong').value = dateStr;

    const lichAmHomNay = doiLichDuongSangAm()

    const lichAmHomNayStr = lichAmHomNay.toString()
    

    document.getElementById('ngay_lich_am').value = lichAmHomNayStr;

    console.log(lichAmHomNay.getCan())

    document.getElementById('lich_am_hom_nay').innerHTML = lichAmHomNayStr + " nam " + lichAmHomNay.getCan().ten + " " + lichAmHomNay.getChi().ten

    document.getElementById('chuyen').addEventListener('click', () => {
            const lichAm = doiLichDuongSangAm()
            document.getElementById('ngay_lich_am').value = lichAm.toString()
    })
})()

function doiLichDuongSangAm() {
    let lichDuong = new Date(document.getElementById('ngay_lich_duong').value);
    return convertSolar2Lunar(lichDuong.getDate(), lichDuong.getMonth() + 1, lichDuong.getFullYear(), 7)
}

function doiLichAmSangDuong() {
    let lichDuong = new Date(document.getElementById('ngay_lich_am').value);
    let lichAm = convertLunar2Solar(lichDuong.getDate(), lichDuong.getMonth() + 1, lichDuong.getFullYear(), 7)

    let lichAmStr = lichAm[2] + "-" + ("0" + lichAm[1]).slice(-2) + "-" + ("0" + lichAm[0]).slice(-2);
    if (lichAm[3]) {
        document.getElementById('nhuan').value = "Thang Nhuan";
    }

    document.getElementById('ngay_lich_am').value = lichAmStr;
}
