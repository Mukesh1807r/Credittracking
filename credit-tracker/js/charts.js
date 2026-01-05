new Chart(document.getElementById("chart"),{
  type:"doughnut",
  data:{
    labels:<?= json_encode(array_keys($catCredits)) ?>,
    datasets:[{
      data:<?= json_encode(array_values($catCredits)) ?>,
      backgroundColor:[
        "#2563eb","#22c55e","#f97316",
        "#dc2626","#8b5cf6","#14b8a6"
      ]
    }]
  },
  options:{
    responsive:true,
    maintainAspectRatio:false,
    plugins:{
      legend:{
        position:'bottom',
        labels:{ boxWidth:12 }
      }
    }
  }
});
