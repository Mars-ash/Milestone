(function(root){
  'use strict';
  const clone=x=>JSON.parse(JSON.stringify(x));
  const activities=['Trial','Sent Sample','Send Document','Audit','Others'];
  let counter=0;
  const uid=()=>root.crypto?.randomUUID?.()||`demo-${Date.now()}-${++counter}`;
  const action=(description,pic,actualDate='')=>({id:uid(),description,pic,actualDate});
  function blank(){return {id:uid(),projectId:'',date:'',activity:'',description:'',customerPic:'',processTrial:'',machine:'',dh:'',material:'',qtyMaterial:'',qtyProcess:'',qtySample:'',eventNote:'',remark:'',status:'Open',problems:[],nextActions:[],feedback:[],files:[]};}
  const emptyAction=()=>action('','');
  const emptyProblem=()=>({id:uid(),description:'',photos:[],actions:[emptyAction()]});
  const emptyFeedback=()=>({id:uid(),date:'',content:'',pic:'',files:[]});
  const filled=(row,keys)=>keys.some(k=>String(row[k]??'').trim()!=='');
  function prepareEditor(row){
    const p=clone(row);delete p.pic;
    if(!p.problems.length)p.problems.push(emptyProblem());
    p.problems.forEach(problem=>{if(!problem.actions.length)problem.actions.push(emptyAction());});
    if(!p.nextActions.length)p.nextActions.push(emptyAction());
    if(!p.feedback.length)p.feedback.push(emptyFeedback());
    return p;
  }
  function cleanProgress(data,current){
    const p=clone(data);delete p.pic;
    const retained=(rows,id)=>rows?.some(row=>row.id===id);
    p.problems=p.problems.map(problem=>{
      const old=current?.problems.find(row=>row.id===problem.id);
      problem.actions=problem.actions.filter(a=>filled(a,['description','pic','actualDate'])||retained(old?.actions,a.id));
      return problem;
    }).filter(problem=>filled(problem,['description'])||problem.photos.length||problem.actions.length||retained(current?.problems,problem.id));
    p.nextActions=p.nextActions.filter(a=>filled(a,['description','pic','actualDate'])||retained(current?.nextActions,a.id));
    p.feedback=p.feedback.filter(f=>filled(f,['date','content','pic'])||f.files.length||retained(current?.feedback,f.id));
    return p;
  }
  function migrate(db){
    db.progress.forEach(p=>{delete p.pic;});
    db.drafts.forEach(d=>{delete d.data.pic;});
    return db;
  }
  function seed(){
    const projects=[
      {id:'jo18',jo:'JO-2026-018',part:'64021-AX010',name:'Bracket fender, RH',customer:'PT Nusa Autocomp',model:'NX-26',doc:'NM/26/018-R02',material:'SPCC',spec:'JIS G3141',thickness:'1,5',width:'180',process:'Blanking, forming',receive:'2026-09-18',t0Target:'2026-10-16',t0Actual:'',planStart:'2026-09-21',planFinish:'2026-10-30',actualStart:'2026-09-22',actualFinish:'',event1:'2026-11-02',event2:'',event3:'',remark:'Pengecekan dimensi mengikuti drawing revisi 02.'},
      {id:'jo19',jo:'JO-2026-019',part:'51280-BX020',name:'Reinforcement pillar',customer:'PT Mitra Teknika',model:'MT-04',doc:'NM/26/019-R01',material:'SPHC',spec:'JIS G3131',thickness:'2.0',width:'240',process:'Piercing, forming',receive:'2026-09-20',t0Target:'2026-10-12',t0Actual:'2026-10-03',planStart:'2026-09-24',planFinish:'2026-10-28',actualStart:'2026-09-24',actualFinish:'',event1:'2026-10-28',event2:'',event3:'',remark:''},
      {id:'jo20',jo:'JO-2026-020',part:'61132-CX030',name:'Stay support, LH',customer:'PT Nusa Autocomp',model:'NX-26',doc:'NM/26/020-R01',material:'SPCC',spec:'JIS G3141',thickness:'1,2',width:'120',process:'Blanking, bending',receive:'2026-09-23',t0Target:'2026-10-15',t0Actual:'2026-10-12',planStart:'2026-09-28',planFinish:'2026-11-06',actualStart:'2026-09-28',actualFinish:'',event1:'',event2:'',event3:'',remark:''}
    ];
    const rows=[['p1','jo18','2026-10-01','Trial','Aditya','Trial pertama untuk pengecekan dimensi dan kondisi permukaan. Ditemukan burr pada area piercing serta deviasi sudut flange.','Verifikasi dimensi setelah adjustment'],['p2','jo19','2026-10-03','Sent Sample','Rina','Pengiriman 10 pcs sample ke customer untuk evaluasi fitment.','Konfirmasi hasil fitment dengan customer'],['p3','jo20','2026-10-04','Trial','Dimas','Trial setting bending dengan material aktual SPCC 1,2.','Lakukan trial ulang pada setting terbaru'],['p4','jo18','2026-10-06','Send Document','Rina','Pengiriman drawing revisi 02 dan hasil pengukuran awal.','Tindak lanjuti persetujuan drawing'],['p5','jo19','2026-10-08','Audit','Aditya','Audit persiapan proses bersama tim customer.','Lengkapi foto kondisi mesin'],['p6','jo20','2026-10-09','Others','Dimas','Diskusi kebutuhan material untuk trial selanjutnya.','Konfirmasi ketersediaan material'],['p7','jo18','2026-10-10','Trial','Aditya','Pengecekan ulang kondisi dies selesai dicatat.',''],['p8','jo20','2026-10-12','Sent Sample','Rina','Pengiriman sample pertama diterima customer.','']];
    const progress=rows.map(([id,projectId,date,activity,nextPic,description,next],i)=>({...blank(),id,projectId,date,activity,description,customerPic:i%2?'Budi':'Hendra',status:i>5?'Closed':'Open',nextActions:next?[action(next,nextPic)]:[]}));
    Object.assign(progress[0],{processTrial:'Piercing & forming',machine:'Press 160T — MC-03',dh:'320,5',material:'SPCC 1,5',qtyMaterial:'30',qtyProcess:'24',qtySample:'6',eventNote:'Trial awal',problems:[{id:'problem-1',description:'Burr pada sisi lubang piercing, tinggi sekitar 0,3 mm.',photos:[],actions:[action('Grinding punch dan periksa ulang clearance.','Aditya','2026-10-02'),action('Ukur tinggi burr pada 5 pcs hasil trial ulang.','Dimas','')]},{id:'problem-2',description:'Sudut flange aktual 88°, belum sesuai drawing 90°.',photos:[],actions:[action('Adjustment stopper pada proses forming.','Dimas','2026-10-03'),action('Verifikasi dimensi setelah adjustment.','Aditya','')]}],nextActions:[action('Verifikasi dimensi setelah adjustment','Aditya'),action('Kirim sample hasil perbaikan ke customer','Rina'),action('Konfirmasi jadwal pengecekan bersama','Aditya')],feedback:[{id:'feedback1',date:'2026-10-05',content:'Mohon kirim hasil pengukuran setelah perbaikan area piercing.',pic:'Hendra',files:[]} ]});
    const d1=blank();Object.assign(d1,{projectId:'jo18',activity:'Trial',description:'Persiapan trial kedua setelah adjustment punch.'});
    const d2=clone(progress[1]);d2.remark='Menunggu informasi hasil pemeriksaan customer.';
    return {version:1,projects,progress,drafts:[{id:'draft1',progressId:null,data:d1,updatedAt:'2026-10-13T01:15:00Z'},{id:'draft2',progressId:'p2',data:d2,updatedAt:'2026-10-13T00:40:00Z'}],applied:{}};
  }
  function saveDraft(db,draft){const item=clone(draft),i=db.drafts.findIndex(d=>d.id===item.id),current=db.progress.find(p=>p.id===item.progressId);if(db.applied[item.id])throw Error('Draft sudah diterapkan. Buka progress terbaru.');if(current?.status==='Closed')throw Error('Reopen progress sebelum mengubah draft.');item.data=cleanProgress(item.data,current);if(i<0)db.drafts.push(item);else db.drafts[i]=item;return item;}
  function validate(p){const errors={};if(!p.projectId)errors.projectId='Pilih JO No.';if(!p.date)errors.date='Isi tanggal aktivitas.';if(!activities.includes(p.activity))errors.activity='Pilih Activity.';return errors;}
  function applyDraft(db,draft){if(db.applied[draft.id])return db.applied[draft.id];const errors=validate(draft.data);if(Object.keys(errors).length)return {errors};if(!db.projects.some(p=>p.id===draft.data.projectId))return {errors:{projectId:'JO tidak ditemukan.'}};const current=db.progress.find(p=>p.id===draft.progressId);if(current?.status==='Closed')throw Error('Reopen progress sebelum menyimpan perubahan.');const row=cleanProgress(draft.data,current);row.id=current?.id||row.id||uid();row.status=current?.status||'Open';const i=db.progress.findIndex(p=>p.id===row.id);if(i<0)db.progress.push(row);else db.progress[i]=row;db.drafts=db.drafts.filter(d=>d.id!==draft.id);db.applied[draft.id]={id:row.id};return {id:row.id};}
  function setStatus(db,id,status){const row=db.progress.find(p=>p.id===id);if(!row)throw Error('Progress tidak ditemukan.');if(!['Open','Closed'].includes(status))throw Error('Status tidak valid.');row.status=status;return row;}
  root.DesignStore={activities,clone,uid,blank,seed,emptyAction,emptyProblem,emptyFeedback,prepareEditor,cleanProgress,migrate,saveDraft,validate,applyDraft,setStatus};
})(globalThis);
