async function register() {
    const name = document.getElementById("name").value.trim();
    const status = document.getElementById("status").value.trim();
    const designation = document.getElementById("designation").value.trim();
    const erp_number = document.getElementById("erp_number").value.trim();
    const appointment_date = document.getElementById("appointment_date").value;
    const current_posting = document.getElementById("current_posting").value.trim();
    const last_posting = document.getElementById("last_posting").value.trim();
    const line_manager = document.getElementById("line_manager").value.trim();
    const segment_head = document.getElementById("segment_head").value.trim();
    const shift = document.getElementById("shift").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const emergency_contact = document.getElementById("emergency_contact").value.trim();
    const profile_image = document.getElementById("profile_image").value.trim();

    const res = await apiRequest("register.php", "POST", {
        name,
        status,
        designation,
        erp_number,
        appointment_date,
        current_posting,
        last_posting,
        line_manager,
        segment_head,
        shift,
        email,
        password,
        emergency_contact,
        profile_image
    });

    document.getElementById("message").innerText = res.message || res.error;
}
