import React, {
  useState,
  ChangeEvent,
  FormEvent,
  JSX,
  useCallback,
} from "react";
import {
  Box,
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  Typography,
  Link as MuiLink,
  OutlinedInput,
} from "@mui/material";
import { AttendanceImportModalProps } from "@/definitions/components";
import { importAttendanceFile } from "./fetch/POST";
function AttendanceImportModal({
  open,
  onClose,
  sampleUrl,
}: AttendanceImportModalProps): JSX.Element {
  const [file, setFile] = useState<File | null>(null),
    handleFileChange = (e: ChangeEvent<HTMLInputElement>): void => {
      if (e.target.files && e.target.files.length > 0)
        setFile(e.target.files[0]);
    },
    handleSubmit = useCallback(
      async (e: FormEvent<HTMLFormElement>): Promise<void> => {
        e.preventDefault();
        if (!file) return;
        const formData = new FormData();
        formData.append("file", file);
        await importAttendanceFile(formData, onClose);
      },
      [importAttendanceFile, onClose]
    );
  return (
    <Dialog
      open={open}
      onClose={onClose}
      fullWidth
      maxWidth='sm'
      id='attendance-import-modal'
    >
      <DialogTitle id='modal-title'>Import Attendance</DialogTitle>
      <form
        onSubmit={handleSubmit}
        id='attendance-import-form'
        encType='multipart/form-data'
      >
        <DialogContent id='modal-body'>
          <Box
            display='flex'
            flexDirection='column'
            gap={2}
            id='import-content'
          >
            <Box id='download-sample' className='mb-6'>
              <Typography
                variant='subtitle1'
                component='label'
                id='download-sample-label'
              >
                Download sample employee CSV file
              </Typography>
              <MuiLink
                href={sampleUrl}
                target='_blank'
                id='download-sample-link'
              >
                <Button
                  variant='contained'
                  size='small'
                  id='download-sample-btn'
                >
                  <i className='ti ti-download' aria-hidden='true' />
                  <span className='ml-1'>Download</span>
                </Button>
              </MuiLink>
            </Box>
            <Box id='file-upload' className='choose-file'>
              <Typography
                variant='subtitle1'
                component='label'
                id='select-csv-label'
              >
                Select CSV File
              </Typography>
              <OutlinedInput
                type='file'
                name='file'
                onChange={handleFileChange}
                inputProps={{ required: true, id: "file-input" }}
              />
            </Box>
          </Box>
        </DialogContent>
        <DialogActions id='modal-footer'>
          <Button
            type='button'
            variant='outlined'
            onClick={onClose}
            id='cancel-btn'
          >
            Cancel
          </Button>
          <Button type='submit' variant='contained' id='upload-btn'>
            Upload
          </Button>
        </DialogActions>
      </form>
    </Dialog>
  );
}
export default React.memo(AttendanceImportModal);
