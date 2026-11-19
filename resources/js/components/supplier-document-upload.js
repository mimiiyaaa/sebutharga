export default function supplierDocumentUpload(config) {
    return {
        ...config.initial, config, suppliers: config.suppliers, fileName: config.existingFile?.name || '', documentUrl: config.existingFile?.url || '', isImage: config.existingFile?.isImage || false,
        loading: false, message: '', rawText: '', method: '', detectedSupplier: '',
        registerOpen: false, registering: false, newSupplier: { company_name: '', customer_name: '', address: '', phone_no: '', email: '' },
        run: 0, nextId: config.initial.items.length + 1,
        releasePreview() { if (this.documentUrl.startsWith('blob:')) URL.revokeObjectURL(this.documentUrl); },
        destroy() { this.releasePreview(); this.run++; },
        addItem() { this.items.push({id: this.nextId++, description:'', quantity:1, unit:'', price:0}); },
        subtotal(item) { return Math.round(Math.max(0, Number(item.quantity)||0)*Math.max(0, Number(item.price)||0)*100)/100; },
        get gross() { return this.items.reduce((sum,item)=>sum+this.subtotal(item),0); },
        money(n) { return Number(n||0).toLocaleString('en-MY',{minimumFractionDigits:2,maximumFractionDigits:2}); },
        async chooseDocument(event) {
            const file = event.target.files[0]; if (!file) return;
            if (!['application/pdf','image/jpeg','image/png'].includes(file.type) || file.size > 10485760) {
                event.target.value=''; this.message=this.config.messages.invalid;
                this.releasePreview();
                this.fileName=config.existingFile?.name || ''; this.documentUrl=config.existingFile?.url || '';
                this.isImage=config.existingFile?.isImage || false; return;
            }
            this.releasePreview();
            this.documentUrl=URL.createObjectURL(file); this.fileName=file.name; this.isImage=file.type.startsWith('image/');
            this.loading=true; this.message=''; this.method=''; this.rawText=''; this.detectedSupplier='';
            this.documentNo=''; this.documentDate=''; this.documentTitle=''; this.supplierId=''; this.totalAmount='';
            this.items=[{id:this.nextId++,description:'',quantity:1,unit:'',price:0}];
            const run=++this.run; const data=new FormData(); data.append('document_file',file);
            try {
                const response=await fetch(config.extractUrl,{method:'POST',headers:{'X-CSRF-TOKEN':config.csrf,'Accept':'application/json'},body:data});
                const result=await response.json(); if(run!==this.run) return;
                if(!response.ok) throw new Error(Object.values(result.errors||{}).flat().join(' ')||result.message||config.messages.failed);
                const d=result.data; this.documentNo=d.document_no||''; this.documentDate=d.document_date||'';
                this.supplierId=d.supplier_id ? String(d.supplier_id) : ''; this.detectedSupplier=d.supplier_name||'';
                this.newSupplier.company_name=this.detectedSupplier;
                if(d.items?.length) this.items=d.items.map(i=>({...i,id:this.nextId++}));
                this.totalAmount=d.total_amount ?? (d.items?.length && config.invoice ? this.gross.toFixed(2) : '');
                this.method=result.method; this.rawText=result.text; this.message=result.message;
                if(!d.items?.length) this.message+=' '+config.messages.noItems;
            } catch(error) { if(run===this.run) this.message=config.messages.failed+' '+error.message; }
            finally { if(run===this.run) this.loading=false; }
        },
        async registerSupplier() {
            if(!this.newSupplier.company_name.trim()||!this.newSupplier.address.trim()) {this.message=config.messages.registrationRequired;return;}
            this.registering=true;
            try {
                const response=await fetch(config.registerUrl,{method:'POST',headers:{'X-CSRF-TOKEN':config.csrf,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify(this.newSupplier)});
                const result=await response.json();
                if(!response.ok) throw new Error(Object.values(result.errors||{}).flat().join(' ')||result.message);
                if(!this.suppliers.some(s=>s.customer_id===result.supplier.customer_id)) this.suppliers.push(result.supplier);
                this.supplierId=String(result.supplier.customer_id); this.registerOpen=false; this.message=result.message;
            } catch(error) {this.message=error.message||config.messages.failed;} finally {this.registering=false;}
        }
    };
}
