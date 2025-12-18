// customer_support_form.js
// JavaScript to handle customer support form submission
// and display success/error messages.

document.getElementById('supportForm').addEventListener('submit', async function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  try {
    const response = await fetch('submit_support_form.php', {
      method: 'POST',
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      alert('Support request submitted successfully! We will get back to you via email.');
      this.reset();
    } else {
      alert('Error: ' + result.error);
    }
  } catch (error) {
    alert('Error submitting request: ' + error.message);
  }
});
