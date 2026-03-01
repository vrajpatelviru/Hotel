//to ready dom

document.addEventListener("DOMContentLoaded",function(){

    // Traveller Dropdown
    const travellerInput = document.getElementById("travellersinput");
    const travellerDropdown = document.getElementById("travellerdropdown");
    const travellerDoneBtn = document.getElementById("travellerdone");

    //open/close drop down when input is clicked

    travellerInput.addEventListener("click",function(){
        travellerDropdown.classList.toggle("d-none");
    });

    //close dropdown when button is clicked

    travellerDoneBtn.addEventListener("click",function(){
        travellerDropdown.classList.add("d-none");
    });

    //counter

    const counters = travellerDropdown.querySelectorAll(".counter");

    //initialize counter
    let counts = {
        adults:2,
        children:0,
        rooms:1
    };

    //update input display
   // Update input display
    function updateTravellersInput() {
        travellerInput.value = `${counts.adults} adults, ${counts.children} children, ${counts.rooms} room${counts.rooms > 1 ? 's' : ''}`;
    }
    updateTravellersInput(); // Initial update 

    //plus/minus

    travellerDropdown.querySelectorAll(".plus,.minus").forEach(button => {
        button.addEventListener("click",function(e){
            e.preventDefault();
            const target = this.dataset.target;
            if(this.classList.contains("plus")){
                counts[target]++;
            }
            else{
                if(counts[target] > (target == "adults" ? 1:0)) counts[target]--;
            }
            travellerDropdown.querySelector(`[data-value="${target}"]`).textContent = counts[target];
            updateTravellersInput();
        });
    });

    //close dropdown when clicking outside
    document.addEventListener("click",function(e){
        if(!travellerInput.contains(e.target) && !travellerDropdown.contains(e.target)){
            travellerDropdown.classList.add("d-none");
        }
    });
});