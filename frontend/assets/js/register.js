async function register(userData) {
    const payload = userData || {
        name: document.getElementById("name").value.trim(),
        status: document.getElementById("status").value,
        designation: document.getElementById("designation").value.trim(),
        designation_code: document.getElementById("designation_code").value.trim(),
        erp_number: document.getElementById("erp_number").value.trim(),
        appointment_date: document.getElementById("appointment_date").value,
        current_posting: document.getElementById("current_posting").value.trim(),
        last_posting: document.getElementById("last_posting").value.trim(),
        line_manager: document.getElementById("line_manager").value.trim(),
        segment_head: document.getElementById("segment_head").value.trim(),
        segment: document.getElementById("segment").value.trim(),
        segment_id: document.getElementById("segment_id").value,
        shift: document.getElementById("shift").value.trim(),
        current_salary: document.getElementById("current_salary").value,
        email: document.getElementById("email").value.trim(),
        phone: document.getElementById("phone").value.trim(),
        emergency_contact: document.getElementById("emergency_contact").value.trim(),
        gender: document.getElementById("gender").value,
        password: document.getElementById("password").value,
        profile_image: document.getElementById("profile_image").value.trim()
    };

    try {
        const res = await apiRequest("register.php", "POST", payload);
        if(document.getElementById("message")) {
            document.getElementById("message").innerText = res.message || res.error;
        }
        return res;
    } catch (err) {
        if(document.getElementById("message")) {
            document.getElementById("message").innerText = "Something went wrong.";
        }
        console.error(err);
    }
}
