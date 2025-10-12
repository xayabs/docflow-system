(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
  typeof define === 'function' && define.amd ? define(['exports'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.th = {}));
}(this, (function (exports) { 'use strict';

  var fp = typeof window !== "undefined" && window.flatpickr !== undefined
      ? window.flatpickr
      : {
          l10ns: {},
      };
  var Lao = {
      weekdays: {
          shorthand: ["ອທ", "ຈັນ", "ອຄ", "ພຸດ", "ພຫ", "ສຸກ", "ເສົາ"],
          longhand: [
              "ວັນອາທິດ",
              "ວັນຈັນ",
              "ວັນອັງຄານ",
              "ວັນພຸດ",
              "ວັນພະຫັດ",
              "ວັນສຸກ",
              "ວັນເສົາ",
          ],
      },
      months: {
          shorthand: [
              "ມກ.",
              "ກພ.",
              "ມນ.",
              "ມສ.",
              "ພພ.",
              "ມຖ.",
              "ກກ.",
              "ສຫ.",
              "ກຍ.",
              "ຕລ.",
              "ພຈ.",
              "ທວ.",
          ],
          longhand: [
              "ມັງກອນ",
              "ກຸມພາ",
              "ມີນາ",
              "ເມສາ",
              "ພຶດສະພາ",
              "ມິຖຸນາ",
              "ກໍລະກົດ",
              "ສິງຫາ",
              "ກັນຍາ",
              "ຕຸລາ",
              "ພະຈິກ",
              "ທັນວາ",
          ],
      },
      firstDayOfWeek: 1,
      rangeSeparator: " ເຖິງ ",
      scrollTitle: "ເລື່ອນເພື່ອເພີ່ມຫຼືລຸດ",
      toggleTitle: "ຄລິກເພື່ອປ່ຽນ",
      time_24hr: true,
      ordinal: function () {
          return "";
      },
  };
  fp.l10ns.lo = Lao; 
  
  exports.Lao = Lao;
  exports.default = fp.l10ns;

  Object.defineProperty(exports, '__esModule', { value: true });

})));