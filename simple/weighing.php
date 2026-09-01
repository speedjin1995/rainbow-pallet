<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Weighbridge System</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #fff;
      margin: 0;
      padding: 10px;
    }
    .container {
      display: flex;
      border: 1px solid #ccc;
      padding: 10px;
    }
    .left-panel {
      width: 40%;
      padding: 10px;
      border-right: 2px solid #333;
    }
    .right-panel {
      width: 60%;
      padding: 10px;
    }
    .status {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: black;
      color: lime;
      font-size: 24px;
      padding: 10px;
    }
    .status .no-connect {
      background: red;
      color: white;
      padding: 5px 10px;
      font-weight: bold;
    }
    .field {
      margin-bottom: 10px;
    }
    .field label {
      display: block;
      margin-bottom: 5px;
    }
    .field input, .field select {
      width: 100%;
      padding: 5px;
      font-size: 16px;
    }
    .weight-box {
      font-size: 24px;
      font-weight: bold;
    }
    .tare-weight {
      color: red;
    }
    .buttons {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
    }
    button {
      padding: 10px 15px;
      font-size: 16px;
      font-weight: bold;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .save-btn { background: gold; }
    .print-btn { background: #4d7cff; color: white; }
    .new-btn { background: lightgreen; }
    .delete-btn { background: red; color: white; }
    .menu-btn, .com-btn { background: grey; color: white; }
    .right-panel h3 {
      margin-top: 0;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      text-align: center;
    }
    th, td {
      border: 1px solid #000;
      padding: 5px;
    }
    .pending-title {
      background: #003366;
      color: red;
      text-align: center;
      padding: 5px;
      font-weight: bold;
    }
    .slip-no {
      color: yellow;
      background: black;
      font-weight: bold;
      padding: 5px;
      margin-bottom: 5px;
      text-align: right;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="left-panel">
      <div class="status">
        <span class="no-connect">NO CONNECT</span>
        <span>100000 kg</span>
      </div>
      <div class="field">
        <label>Weight Status</label>
        <select>
          <option>Dispatch</option>
        </select>
      </div>
      <div class="field">
        <label>Lorry Plate</label>
        <input type="text" value="PPP8888">
      </div>
      <div class="field">
        <label>In Weight</label>
        <input type="text" value="10,000 kg">
      </div>
      <div class="field">
        <label>Date / Time</label>
        <input type="text" value="14/05/2025 - 14:30:08PM">
      </div>
      <div class="field">
        <label>Out Weight</label>
        <input type="text" value="30,000 kg">
      </div>
      <div class="field">
        <label>Date / Time</label>
        <input type="text" value="14/05/2025 - 14:30:08PM">
      </div>
      <div class="field weight-box tare-weight">Tare Weight: 0 kg</div>
      <div class="field weight-box">Nett Weight: 20,000 kg</div>
      <div class="buttons">
        <button class="save-btn">SAVE</button>
        <button class="print-btn">PRINT</button>
      </div>
      <div class="buttons">
        <button class="new-btn">NEW RECORD</button>
        <button class="delete-btn">DELETE</button>
      </div>
      <div class="buttons">
        <button class="menu-btn">MENU WEIGHT</button>
        <button class="com-btn">COM SETTING</button>
      </div>
    </div>
    <div class="right-panel">
      <div class="slip-no">CURRENT SLIP NO: D/2505/0001</div>
      <div class="pending-title">PENDING 2ND WEIGHT</div>
      <table>
        <thead>
          <tr>
            <th>NO</th>
            <th>STATUS</th>
            <th>PLATE NO</th>
            <th>IN WEIGHT</th>
            <th>1ST - DATE / TIME</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>DISPATCH</td>
            <td>PPP9999</td>
            <td>5,000 KG</td>
            <td>14/05/2025 - 14:30:08PM</td>
          </tr>
          <tr>
            <td>2</td>
            <td>RECEIVING</td>
            <td>WWW333</td>
            <td>6,000 KG</td>
            <td>13/05/2025 - 14:30:08PM</td>
          </tr>
          <!-- More rows can be added -->
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
