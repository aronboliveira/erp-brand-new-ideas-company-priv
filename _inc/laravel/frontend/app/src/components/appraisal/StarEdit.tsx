import React, { JSX } from "react";
import { Box, Divider, Grid, Typography } from "@mui/material";
import { PerformanceRatingsProps } from "@/definitions/components";
import { Type } from "@/definitions/helpers";
export default function AppraisalStarEdit({
  performanceTypes,
  ratings,
  rating,
}: PerformanceRatingsProps): JSX.Element {
  return (
    <Box id='performance-ratings'>
      <Grid container spacing={2}>
        <Grid
          item
          xs={5}
          sx={{ textAlign: "end", ml: "51px" }}
          id='indicator-header'
        >
          <Typography variant='h5'>Indicator</Typography>
        </Grid>
        <Grid item xs={4} sx={{ textAlign: "end" }} id='appraisal-header'>
          <Typography variant='h5'>Appraisal</Typography>
        </Grid>
        {performanceTypes.map(performanceType => (
          <React.Fragment key={performanceType.id}>
            <Grid
              item
              xs={12}
              sx={{ mt: 3 }}
              id={`pt-heading-${performanceType.id}`}
            >
              <Typography variant='h6'>{performanceType.name}</Typography>
              <Divider sx={{ mt: 0 }} />
            </Grid>
            {performanceType.types.map((t: Type) => (
              <React.Fragment key={t.id}>
                <Grid item xs={4} id={`type-name-${t.id}`}>
                  <Typography>{t.name}</Typography>
                </Grid>
                <Grid item xs={4} id={`ratings-fieldset-${t.id}`}>
                  <Box
                    component='fieldset'
                    className='rating'
                    id={`demo-${t.id}`}
                  >
                    {[5, 4, 3, 2, 1].map(value => (
                      <React.Fragment key={value}>
                        <input
                          className='stars'
                          type='radio'
                          id={`technical-${value}*-${t.id}`}
                          name={`ratings[${t.id}]`}
                          value={value}
                          checked={ratings[t.id] === value}
                          disabled
                        />
                        <label
                          className='full'
                          htmlFor={`technical-${value}*-${t.id}`}
                          title={
                            value === 5
                              ? "Awesome - 5 stars"
                              : value === 4
                              ? "Pretty good - 4 stars"
                              : value === 3
                              ? "Meh - 3 stars"
                              : value === 2
                              ? "Kinda bad - 2 stars"
                              : "Sucks big time - 1 star"
                          }
                        ></label>
                      </React.Fragment>
                    ))}
                  </Box>
                </Grid>
                <Grid item xs={4} id={`rating-fieldset-${t.id}`}>
                  <Box
                    component='fieldset'
                    className='rating'
                    id={`demo1-${t.id}`}
                  >
                    {[5, 4, 3, 2, 1].map(value => (
                      <React.Fragment key={value}>
                        <input
                          className='stars'
                          type='radio'
                          id={`technical-${value}-${t.id}`}
                          name={`rating[${t.id}]`}
                          value={value}
                          checked={rating[t.id] === value}
                        />
                        <label
                          className='full'
                          htmlFor={`technical-${value}-${t.id}`}
                          title={
                            value === 5
                              ? "Awesome - 5 stars"
                              : value === 4
                              ? "Pretty good - 4 stars"
                              : value === 3
                              ? "Meh - 3 stars"
                              : value === 2
                              ? "Kinda bad - 2 stars"
                              : "Sucks big time - 1 star"
                          }
                        ></label>
                      </React.Fragment>
                    ))}
                  </Box>
                </Grid>
              </React.Fragment>
            ))}
          </React.Fragment>
        ))}
      </Grid>
    </Box>
  );
}
