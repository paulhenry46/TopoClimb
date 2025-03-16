@props(['name', 'adress'])

<div>
<div class=" flex items-center p-0 mt-6 overflow-hidden bg-center bg-cover h-96 rounded-2xl" style="background-image: url('https://escalade-gourdon.fr/wp-content/uploads/2018/11/cours-escalade.jpg'); background-position-y: 50%">

</div>
</div>

<div class="rounded-2xl bg-center bg-cover *bg-gradient-to-tl *from-gray-600 *to-gray-400  z-10 h-96 -mt-96" style="
    background-image: linear-gradient(to left top, rgba(75, 85, 99, 0.6), rgba(156, 163, 175, 0.6));">
</div>

<div class="rounded-md ml-10 min z-40 min-h-24 -mt-36 w-fit bg-gray-900 bg-opacity-30">
    <div class=" py-3 px-3 flex mb-2">
      <img class="size-24 rounded-md object-cover" src="http://127.0.0.1:8000/storage/profile-photos/PotQqCJExst4D8nmxKQAR7jFFzL1sgkULnKx2kE2.png" alt="Admin">
      <div class="ml-3 grid grid-rows-2">
        <div class="font-semibold text-white content-center text-3xl">{{$name }}</div>
        <div class=" text-white content-top">{{ $adress }}</div>
      </div>
    </div>
</div>